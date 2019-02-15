<?php

namespace App\Model\AutoHAC;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\AutohacController;
use Log;
use Mail;

class AutohacUser extends Model
{
    public function sendMsg(string $msg) {
        if (!is_null($this->telegram_chat_id)) {
            $curl = curl_init('https://api.telegram.org/' . env('TELEGRAM_BOT') . '/sendMessage');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode([
                'chat_id'=>$this->telegram_chat_id, 'text'=>$msg
            ]));
            $return = curl_exec($curl);
            curl_close($curl);
            return json_decode($return, true);
        } elseif (!is_null($this->kik_name)) {
            $headers = [
                'Content-Type: application/json',
                'Authorization: Basic '. base64_encode(env('KIK_BOT'))
            ];
            $curl = curl_init('https://api.kik.com/v1/message');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_HEADER, 1);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode([
                'messages' => [['body'=>$msg, 'to'=>$this->kik_name, 'type'=>'text', 'chatId'=>$this->kik_chat_id]]
            ]));
            $return = curl_exec($curl);
            curl_close($curl);
            return json_decode($return, true);
        } elseif (!is_null($this->verizon_num) ) {
            $txt = "AutoHAC: " . $msg;
            $ti = 0;
            while ($ti < strlen($txt)) {
                $tdivide = min(strlen($txt), 150);
                $mxt = substr($txt, $ti, $tdivide);
                if ($ti > 0)
                    $mxt = "..." . $mxt;
                $ti += $tdivide;
                if ($ti < strlen($txt))
                    $mxt .= "...";
                mail($this->verizon_num . '@vtext.com', '', $mxt, 'From: ' . env('SMS_FROM_ADDRESS'));
            }
        }
    }
    
    public function isRecognized() {
        return !is_null($this->username);
    }
    
    public function courses() {
        return $this->hasMany('App\Model\AutoHAC\AutohacCourse', 'user_id');
    }
    
    public function school() {
        return $this->belongsTo('App\Model\AutoHAC\AutohacSchool', 'school_id');
    }
    
    public function getSignupCode() {
        if (is_null($this->signup_code) || $this->signup_code < 100000 || $this->updated_at->lt((new \Carbon\Carbon())->subDays(1))) {
            $this->signup_code = mt_rand(100000, 999999);
        }
        return $this->signup_code;
    }
    
    public function deactivate() {
        $this->sendMsg('Your account has been deactivated. To reactivate: ' . env('APP_URL'));
        $this->verizon_num = null;
        $this->kik_name = null;
        $this->kik_chat_id = null;
        $this->telegram_chat_id = null;
        $this->getSignupCode();
        $this->save();
    }
    
    public function textType() {
        if (!is_null($this->telegram_chat_id)) {
            return 'Telegram';
        } elseif (!is_null($this->kik_name)) {
            return 'Kik';
        } elseif (!is_null($this->verizon_num) && $this->isActive()) {
            return 'Verizon';
        } else {
            return '';
        }
    }
    
    public function isActive() {
        return $this->signup_code == null && $this->updated_at != null;
    }
    
    public static function withSignupCode($signupCode) {
        $tryUsers = AutohacUser::where('signup_code', $signupCode)->get();
        $user = null;
        foreach ($tryUsers as $tryUser) {
            if ($tryUser->getSignupCode() == $signupCode) {
                $user = $tryUser;
            }
        }
        return $user;
    }
    
    public static function adminUser() {
        return AutohacUser::where('real_name', env('ADMIN_FULL_NAME'))->first();
    }
}
