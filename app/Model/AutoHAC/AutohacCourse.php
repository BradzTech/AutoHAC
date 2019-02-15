<?php

namespace App\Model\AutoHAC;

use Illuminate\Database\Eloquent\Model;

class AutohacCourse extends Model
{
    public $timestamps = false;
    
    public function commit() {
        $existCourse = AutohacCourse::where([
            ['user_id', $this->user_id],
            ['user_index', $this->user_index],
            ['name', $this->name],
            ['mp', $this->mp]
        ])->first();
        if (isset($existCourse)) {
            $existCourse->points = $this->points;
            $existCourse->max_points = $this->max_points;
            $existCourse->percent = $this->percent;
            $existCourse->save();
            return [$existCourse->id, false];
        } else {
            $existCourse = AutohacCourse::where([
                ['user_id', $this->user_id],
                ['user_index', $this->user_index]
            ])->first();
            if (isset($existCourse)) {
                $existCourse->delete();
            }
            $this->save();
            return [$this->id, true];
        }
    }
    
    public function user() {
        return $this->belongsTo('App\Model\AutoHAC\AutohacUser', 'user_id');
    }
    
    public function assignments() {
        return $this->hasMany('App\Model\AutoHAC\AutohacAssignment', 'course_id');
    }
    
    public function shortName() {
        $titleWords = explode(" ", $this->name);
        foreach ($titleWords as $word) {
            if (ctype_alpha(substr($word, 0, 1)) && (strlen($word) > 3)) {
                $courseTitle = substr($word, 0, 3);
                return $courseTitle;
            }
        }
    }
}
