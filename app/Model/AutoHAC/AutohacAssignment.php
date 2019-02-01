<?php

namespace App\Model\AutoHAC;

use Illuminate\Database\Eloquent\Model;
use App\Model\AutoHAC\AutohacCourse;
use App\Model\AutoHAC\AutohacUser;

class AutohacAssignment extends Model
{
    public function commit($dontSend) {
	    $existAssign = AutohacAssignment::where([
	    	['course_id', $this->course_id],
	    	['due_date', $this->due_date],
	    	['name', $this->name],
	    	['course_type', $this->course_type]
	    ])->first();
	    if (isset($existAssign)) {
		    if ($existAssign->points != $this->points || $existAssign->max_points != $this->max_points) {
		    	$existAssign->points = $this->points;
		    	$existAssign->max_points = $this->max_points;
				$existAssign->save();
				//TODO: zerotime
				$existAssign->notif();
		    }
	    } else {
		    $this->save();
		    if ($this->points >= 0 && !$dontSend) {
			    $this->notif();
		    }
	    }
    }
    
    public function course() {
	    return $this->belongsTo('App\Model\AutoHAC\AutohacCourse', 'course_id');
    }
    
    private function notif() {
	    $course = $this->course;
	    $points = $this->points;
	    if ($points < 0) {
		    $points = "_";
	    }
	    $msg = '[' . $course->shortName() . '] ' . $this->displayName() . ': ' . $this->points . '/' . $this->max_points;
	    if ($this->points <= 0) {
			$msg .= ", due " . $this->due_date;
		}
	    $course->user->sendMsg($msg);
    }
    
    private function displayName() {
	    $ct = $this->course_type;
		switch ($ct) {
			case "Homework":
				$ct = "HW";
			case "Classwork":
				$ct = "CW";
		}
		$name = $this->name;
		if (strlen($name) < 15 && (strpos($this->name, $this->course_type) === false && strpos($this->name, $ct) === false)) {	//If short name, append assignment type
			$name .= " (" . $ct . ")";
		}
		return $name;
    }
}
