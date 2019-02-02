<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Http\Requests;
use App\Model\AutoHAC\AutohacSchool;
use App\Model\AutoHAC\AutohacUser;
use App\Model\AutoHAC\AutohacCourse;
use App\Model\AutoHAC\AutohacAssignment;
use Log;

class AutohacController extends Controller
{
	// Enter a single user's full name here if needed for debugging
	//public $onlyUser = '';
	
	/**
	 * Called when a request is made to the site's homepage.
	 * @return The appropriately populated homepage view.
	 */
	public function getPostHome(Request $request) {
		$vargs = [];
		$username = $request->input('UserName');
		$password = $request->input('Password');
		$distri = $request->input('Distri');
		$phoneNum = $request->input('PhoneNum');
		if ($request->isMethod('post') && $username != null && $password != null && $distri != null) {
			$user = AutohacUser::where([
				['username', $username],
				['password', $password],
				['school_id', $distri]
			])->first();
			if (is_null($user)) {
				$user = new AutohacUser;
				$user->username = $username;
				$user->password = $password;
				$user->school_id = $distri;
			} elseif ($request->input('deactivate') == 'true') {
				$user->deactivate();
				$user->save();
			}
			$vargs['uname'] = $user->username;
			$vargs['pword'] = $user->password;
			$vargs['distri'] = $user->school_id;
			if ($user->isActive()) {
				$vargs['activeAccount'] = $user->textType();
			} elseif (isset($phoneNum)) {
				$user->verizon_num = trim($phoneNum);
				$vargs['phoneNum'] = $phoneNum;
				$vargs['signupCode'] = $user->getSignupCode();
				$user->save();
				$user->sendMsg('Welcome! Please respond with the verification number to continue.');
			} else {
				$doms = $this->retrievePages($user, []);
				if (count($doms) > 0) {
					$vargs['newuser'] = true;
					$user->real_name = $this->getRealName($doms);
					if (isset($user->real_name)) {
						$vargs['realname'] = $user->real_name;
					}
					$vargs['signupCode'] = $user->getSignupCode();
					$user->save();
				} else {
					$vargs['wrongpword'] = true;
				}
			}
		} elseif ($request->privacy) {
			$vargs['privacy'] = true;
		} else {
			$vargs['homepage'] = true;
		}
		$vargs["districts"] = AutohacSchool::all();
		$vargs["assetUrl"] = env("HAC_ASSET_URL");
		return view('autohac.home', $vargs);
	}
	
	/**
	 * Get the privacy page.
	 * @return A view.
	 */
	public function getPrivacy(Request $request) {
		$request->privacy = true;
		return $this->getPostHome($request);
	}
	
	/**
	 * Query the target Home Access Center page for a given user.
	 * @return The retrieved pages.
	 */
	private function retrievePages(AutohacUser $user, array $requestPages) {
		array_unshift($requestPages, $this->URLHome);
		// Get a path to a temporary cookie file
		$cookieFile = tempnam(sys_get_temp_dir(), 'autohac_cookie_');
		$rooturl = $user->school->root_url;
		$pages = [];
		foreach ($requestPages as $rpage) {
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $rooturl . $rpage);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($curl, CURLOPT_COOKIEFILE, $cookieFile);
			curl_setopt($curl, CURLOPT_COOKIEJAR, $cookieFile);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			$fields = [];
			if ($rpage == $this->URLHome) {
				// Set POST arguments for login
				$fields = [
					"Database" => urlencode("10"),
					"LogOnDetails.UserName" => urlencode($user->username),
					"LogOnDetails.Password" => urlencode($user->password)
				];
			} elseif ($rpage == $this->URLAssignments && $user->school->current_mp > 0) {
				// Set POST arguments to trick the Assignments page to load
				$fields = [
					"__EVENTTARGET" => "ctl00%24plnMain%24btnRefreshView",
					"__EVENTARGUMENT" => "",
					"__VIEWSTATE" => "Ra4EXk8WRzm6G4SnUkEbbCD%2FwaI1oj9csAzbugxGPWrPAakKVZp8BIHXYTun%2BeU5ivhzuvEo9Jb5P2ySml7QhSMnT2vXUDVZ8MYoNieP6OFBBlAPEHlZiBolrTSShANBAw9oEDKxFj4Nl2B1qrn7K1qJ8jhFjV5jfZka6YsCOGDf4rHKKfQtIm4gISrP0f7F%2BgzE06kDSfwTmPzQBO7qXt%2BCcPhA0LviqYSeaav7VCdAuVXBBRfVVBo6%2B24BPm0xAyZQ6o2x7rVPJKvLIL%2BiRSwUIUiKu%2BXHnhVuW9C7fg4ijWmJtCnRfU4dfVJLJlsCS2Il73ets4LFTRO%2Fen0PARc56oMn7lV%2FfBH%2FJlO3eMxRA%2FEE045lLyl6XtX4Wp2uGC5srfSEMpe6A2dZamTncwwexEGspT0Ow0FIoIlS5097g%2F4pQpGsHo1KtiLaOREVZV89%2BoqFSzfclyZFsWcNZyQP7xTt50ZVL0s3XhU0ayeIKqOTrv0nFPrLxKRIgOeYpFD37gtruiwDZmJLDL%2BXU3fg2Y0gf6lWeC%2BPxC5ECFQFcZM2219mYMJyCfSqK0PqnnvKVhdR5sDq5xTxHDXwIbDhM3lqhPxgiqDDCuqZUx9yXf6G%2FE1xdUaD5QJQoE5DMu0AQXFmDG5DHfrWL4o9AiNW153ZGajlpOiJvUFVHaMweXfQ8bQk3lSG8mbABKzoFeM%2BhbZjhhMa2V8qJqufSSGCvohu5NGqJeMYBIB6SYP%2FsJ224E%2BAf%2BaTj0Q70XjesDbC%2FxCDzC0UAo6wIYc%2F%2FoXQmyrQdW6bMzmaCNI1mCEUjo46rJnS76wD4nk0Cq9QfR0osjNYSh23vMDvugUxufMjW66xBNNhqVAdHC1VJvrBiGjRseAMJIzEsL%2B6tTdGny7bmKV2BOjJORV5GNV7R3xTCuAH%2FNm%2F75jgwj4LoBya4L0XQ1f2tC9ieb61PVVBuxMMyMKm2EraJH%2FNAnvU4za6s%2BbbTOIF44m%2BE7ig3KgYObpXb3hxcKSejJmbocruZRTO3Jd3uiSyQGB2pk0DH2VBQSTIggiCMbk%2F2sxgxiGrojmRYNl6tyRlF4rmgBSZ",
					"__VIEWSTATEGENERATOR" => "B0093F3C",
					"__EVENTVALIDATION" => "1l5Lxy0P%2FLyZXvSfM%2FASu0GtHiiYbkG47gG%2FVAj0EPnPuw0KELbXVYfoN3aHMs4Cr6CBNjCrkZq%2FEcewbdNV%2FVJCZne71vQbSKWWIBf4JknHvFWio1fbj6EIZww15NWODO8awKjmYhhqqMljk%2BTs2Xq4YxYeQVOeb5JtL%2BgcW0tM45ClVAS0CgeDVVolkdS0RfkmkxI7ERV2pYNo7fOKpVUOLtEaMP0k2GvZTeTtrLNrZ1tgWZQjVBtIaT6fF0cygukl4IwarCCs%2Bjc1Lh0nU%2BzWolPrSbZ7kP%2F8HyP45352DsMLBK9xPhA4BzI61A%2Fhr80bo98mKqrxY9C9%2FC78fe%2FdDSiZWpvg0TwOyPVLVyAblo9j7nUodJxBBUb30vFY%2FKbNrGczUu93FfGb%2FSZevPS27qZY9SCxjribdfJ7YZmvV2a6jmMt0uZ9oE8KBbisQVaxZhEdU8kSmBdaGavwStuVdfXzFGydvn%2B%2FET6tvstdWsFIC1gNA6yMei8AsRacZtx4yalpv8ZskgfEI4f4ksPUt648ROM0DrI%2B8Me2iXuQMhyytV5%2FM1DPlXwlY6AAz6uqdD4ndL3NBw%2BktuKyqO4%2BApT0m6P7TkMydB4neaCYjLHNhBMn4qqAvzQsba5E%2BHzOytD%2B6L3u6IWtijzEC1fPXlolA2pAkFtewV6zJu0%2Bh24%2F9gYPyOiT86BLM51YQy2P7j%2BmrRVSB5WBi1LK5zeBMW8BHT7r1tMDSGd0wBvx58fK2XcBxMlv5ql3GEDgu6g4BPNVlXe2GYK0HkLvEF%2BInB%2F7akEmApnMmVCNURQvc4ldf5w6Axj5ZPrBxYw1lwU2nF82UV6%2BurrzeaUIR6khViLAHEDtWYhmq1qAdBzbDypKndboXIwlxz5VvS1z%2FfWYN8TF%2BO3IPkng7mnVI3VINDnC8ISFCE%2Btx9oj25uZnQy926myUT%2FT7nwYSJWNpLhwpRSaY1fmmDlESjM48xPRfy4NKeXn%2FQhgzqQYG4tG%2BT75GMAlqkReZsyBMm1ed%2BOzLatDcvipKVvTy6J8BT6RP4z%2BXammg%2BfsrO60EZyvGS%2F356ctSeed%2F3L3nrAH%2FElySeVo4RGM7ykgL%2BuScIIM4NgJ1BIR7PPyALOcYJS6SEaq%2FR1OLj2VRLQmLPQ%2FjvqnBLdWD3BjuVfG5yKvgSRFo9hueLfL46QUSY7oRuHet8DMhYuaub9KlxqlzZD2I55Iq19Uv%2FODsceJFYIZE9Tjqy858ZXs%2FfHGdD9UtuRMZbCVx2iaUO%2BUXQG6t3x5Xrq95tBGVJrqr9j%2FjE5lL9nNtOP%2BSFjzYXPWImyGND9ZGF%2F9pfnWDi3jeJxL1DWGxCFrqtdyidVWIPDe1Tvi81TRuBs7zNtaNCfj1lnEr55Bnfd0s3EUwYZm9AAaHoSpyidTLL%2BvlzrNJi5uRCshJR8yaHv1ol9%2FpZz84zqj%2FuphGvc%2BY8VDWHeMNQ%2BKd%2BA5aPQxaMu%2FappTkIsz3bDVmEmEAYDPfOw4x%2B8lykxpSzw%3D",
					"ctl00%24plnMain%24ddlReportCardRuns" => $user->school->current_mp
				];
			}
			if (count($fields) > 0) {
				$fieldsStr = "";
				foreach ($fields as $key=>$value) { $fieldsStr .= $key.'='.$value.'&'; }
				$fieldsStr = rtrim($fieldsStr, '&');
				curl_setopt($curl, CURLOPT_POST, 1);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $fieldsStr);
				curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
			}
			$cres = curl_exec($curl);
			if ($cres) {
				//Return if login failed
				if ($rpage == $this->URLHome && starts_with(curl_getinfo($curl, CURLINFO_EFFECTIVE_URL), $rooturl . "/Account/LogOn")) return $pages;
				$dres = new \domDocument;
				$dres->preserveWhiteSpace = false;
				if (@$dres->loadHTML($cres)) {
					$pages[$rpage] = $dres;
				}
			} else {
				Log::warning("cURL Error! " . curl_error($curl));
			}
			curl_close($curl);
		}
		// Delete the temporary cookie file
		if (file_exists($cookieFile)) {
			unlink($cookieFile);
		}
		return $pages;
	}
	
	/**
	 * Query a user's Home Access Center page, and parse it for all of the
	 * Courses and Assignments. If any changes exist, update the database or
	 * send messages as necessary.
	 */
	public function syncCourses(AutohacUser $user) {
		$pages = $this->retrievePages($user, [$this->URLAssignments]);
		if (isset($pages[$this->URLAssignments])) {
			$dres = $pages[$this->URLAssignments];
			$mpbox = $dres->getElementById("plnMain_ddlReportCardRuns");
			$mp = 0;
			if (!isset($mpbox->childNodes)) {
				return;
			}
			foreach ($mpbox->childNodes as $mpbo) {
				foreach ($mpbo->attributes as $mpba) {
					if ($mpba->name == "selected") {
						$mp = $mpbo->nodeValue;
					}
				}
			}
			$items = $dres->getElementsByTagName('tr');
			$xpath = new \DOMXPath($dres);
			$xres = $xpath->query("//*[@class='AssignmentClass']");
			$numCourses = 0;
			foreach ($xres as $classid => $class) {
				$course = New AutohacCourse;
				$course->user_id = $user->id;
				$course->user_index = $classid;
				$course->mp = $mp;
				$assignments = [];
				$numCourses++;
				foreach ($class->childNodes as $noden) {
					if (isset($noden->childNodes) && $noden->childNodes->length > 0) {
						foreach ($noden->childNodes as $node) {
						    if ($node->localName == "a") {
						    	$course->name = trim($node->nodeValue);
						    } elseif ($node->localName == "table") {
							  	foreach ($node->childNodes as $nod) {
							  		if ($nod->localName == "tr") {
									    $infoa = [];    //Array of info for this grade
										foreach ($nod->childNodes as $no) {
										    if ($no->localName == "td") {
											    $infoa[] = trim($no->nodeValue);
										    }
									  	}
									  	if (is_numeric(substr($infoa[0], 0, 2))) {    //Verify this is a grade, vs a header
										  	$assignment = New AutohacAssignment;    //Converts info array to object
										  	$assignment->due_date = substr($infoa[0], 0, 5);
										  	$assignment->name = trim(str_replace("*", "", $infoa[2]));
										  	$assignment->course_type = $infoa[3];
										  	if (is_numeric($infoa[4]))
										  		$assignment->points = (float)$infoa[4];
										  	else
										  		$assignment->points = -1;		  		
										  	$assignment->max_points = (float)$infoa[5];
										  	$shouldAddAssign = true;
										  	foreach ($assignments as $cassign) {
											  	if ($cassign->name == $assignment->name) {
												  	$shouldAddAssign = false;
											  	}
										  	}
										  	if ($shouldAddAssign) {
										  		$assignments[] = $assignment;
										  	}
									  	}
								    }
							  	}
						    } elseif (isset($node->childNodes) && $node->childNodes->length > 0) {
							    foreach ($node->childNodes as $nodf) {
								    if ($nodf->localName == "span") {
									    foreach ($nodf->childNodes as $nodg) {
										    if ($nodg->localName == "div") {
											    foreach ($nodg->childNodes as $nodh) {
												    if ($nodh->localName == "table") {
													    $warr = [];    //2D array: 1=GradeCategories, 2=CategoryInfo
												    	foreach ($nodh->childNodes as $nod) {
													  		if ($nod->localName == "tr") {
															    $scorearr = [];
																foreach ($nod->childNodes as $no) {
																    if ($no->localName == "td") {
																	    $scorearr[] = $no->nodeValue;
																    }
															  	}
															  	$warr[] = $scorearr;
														    }
													  	}
													  	array_shift($warr);    //Removes headers from the array
													  	if (! is_numeric(end($warr)[1])) {    //If the course is weighted, 
														  	$rpoints = 0;
														  	$rmax = 0;
														  	$wpoints = 0;
														  	foreach ($warr as $wcat) {
																$rpoints += (float)$wcat[1];
																$rmax += (float)$wcat[2];
														  	}
														  	$course->points = $rpoints;
														  	$course->max_points = $rmax;
														  	
														  	$weights = end($warr)[4];
														  	$weightperc = end($warr)[5];
														  	$course->percent = ($weightperc / $weights) * 100;
													  	} else {
														  	$course->points = end($warr)[1];
														  	$course->max_points = end($warr)[2];
														  	$course->percent = $course->max_points == 0 ? 0: ($course->points / $course->max_points) * 100;
													  	}
												    }
											    }
										    }
									    }
								    }
							    }
						    }
						}
					}
			    }
				$courseCom = $course->commit();
				foreach ($assignments as $assignment) {
					$assignment->course_id = $courseCom[0];
					$assignment->commit($courseCom[1]);
				}
			}
			if ($numCourses > 1) { // Delete excess courses from previous year, such as gym
			    $excessCourses = AutohacCourse::where([
			    	['user_id', $user->id],
			    	['user_index', '>=', $numCourses]
			    ])->get();
				foreach ($excessCourses as $excessCourse) {
					$excessCourse->delete();
				}
			}
		}
	}
	
	/**
	 * Handle a message recieved from the Kik API (deprecated).
	 * @return JSON response.
	 */
	public function postKik(Request $request) {
		$jmessages = $request->json()->all()['messages'];
		if (!is_null($jmessages)) {
			foreach ($jmessages as $jmess) {
				$kikName = $jmess['from'];
				$kikChatId = $jmess['chatId'];
				if (isset($jmess['body'])) {
					$kikMsg = trim($jmess['body']);
					$user = AutohacUser::where('kik_name', $kikName)->first();
					if (is_null($user)) {
						$user = AutohacUser::withSignupCode($kikMsg);
						if (is_null($user)) {
							$user = new AutohacUser;
						}
						$user->kik_name = $kikName;
						$user->kik_chat_id = $kikChatId;
					}
					if ($kikChatId != $user->kik_chat_id) {
						$user->kik_chat_id = $kikChatId;
						$user->save();
					}
					$this->handleMsg($user, $kikMsg);
				}
			}
		}
		return response()->json(true);
	}
	
	/**
	 * Handle a message recieved from the Telegram API.
	 * @return JSON response.
	 */
	public function postTelegram(Request $request) {
		$jupdate = $request->json()->all();
		if (isset($jupdate['message'])) {
			$jmess = $jupdate['message'];
			$chatId = $jmess['chat']['id'];
			if (isset($jmess['text'])) {
				$jtxt = trim($jmess['text']);
				$user = AutohacUser::where('telegram_chat_id', $chatId)->first();
				if (is_null($user)) {
					$user = AutohacUser::withSignupCode($jtxt);
					if (is_null($user)) {
						$user = new AutohacUser;
					}
					$user->telegram_chat_id = $chatId;
				}
				$this->handleMsg($user, $jtxt);
			}
		}
		return response()->json(true);
	}
	
	/**
	 * Handle a message recieved from the Verizon SMS-to-email gateway.
	 */
	public function handleVerizon(string $fromNum, string $msgg) {
		$msg = trim($msgg);
		$user = AutohacUser::where('verizon_num', $fromNum)->first();
		if (is_null($user) || (!$user->isActive() && $user->getSignupCode() != $msg)) {
			$user = new AutohacUser;
			$user->verizon_num = $fromNum;
		}
		$this->handleMsg($user, $msg);
	}
	
	/**
	 * Handle a given string input message from a given user.
	 */
	private function handleMsg(AutohacUser $user, string $msg) {
		if ($user->isRecognized()) {
			$m = strtoupper($msg);
			if (!$user->isActive()) {
				$user->sendMsg("Subscription complete! You will get a msg whenever a new grade is posted. Type ? for more commands. Please note: this program comes with no guarantees beyond privacy. Type ! <msg> to send a question/suggestion. Type STOP to unsub.");
				$user->signup_code = null;
				$user->save();
				$this->syncCourses($user);
			} elseif (starts_with($m, "!")) {
				if (trim($m) == "!") {
					$user->sendMsg("To send a message to Brad, type ! then a message.");
				} else {
					AutohacUser::adminUser()->sendMsg($user->real_name . ":" . $user->id . ": " . $msg);
				}
			} elseif (starts_with($m, "?")) {
				$user->sendMsg("?=This msg\n!=Support\nSTOP=Delete account\nAnything else = Average grades");
			} elseif (starts_with($m, "STOP")) {
				$user->deactivate();
			} elseif (starts_with($m, '@ALL') && $user == AutohacUser::adminUser()) {
				$amsg = substr($msg, 4);
				$tusers = AutohacUser::where('signup_code', null)->get();
				foreach ($tusers as $tuser) {
					$tuser->sendMsg($amsg);
				}
			} elseif (starts_with($m, '@ALV') && $user == AutohacUser::adminUser()) {
				$amsg = substr($msg, 4);
				$tusers = AutohacUser::where([['signup_code', null], ['verizon_num', '<>', null]])->get();
				foreach ($tusers as $tuser) {
					$tuser->sendMsg($amsg);
				}
			} elseif (starts_with($m, '@') && $user == AutohacUser::adminUser()) {
				$uid = substr($msg, 1, 3);
				$amsg = substr($msg, 4);
				$tuser = AutohacUser::where('id', $uid)->first();
				if (!is_null($tuser)) {
					$tuser->sendMsg($amsg);
				}
			} else {
				$str = '';
				foreach ($user->courses as $course) {
					if ($course->max_points > 0) {
						$str .= "\n" . $course->shortName() . ': ' . (float)$course->points . '/' . (float)$course->max_points . ' ' . $course->percent . '%';
					}
				}
				if ($str == '') {
					$str = "Error: no courses with any grades.";
				}
				$user->sendMsg($str);
			}
		} else {
			$user->sendMsg("Welcome to AutoHAC! Please enter your signup code to continue. Sign up here: " . env('APP_URL'));
		}
	}
	
	/**
	 * Queries to see if a user is valid, and if so, gets their real name.
	 * @return A string.
	 */
	private function getRealName(array $doms) {
		$rnxp = new \DOMXPath($doms[$this->URLHome]);
		$rnx = $rnxp->query("//*[@class='sg-banner-menu-element sg-menu-element-identity']")->item(0);
		$realname = "";
		foreach ($rnx->childNodes as $rnn) {
			if (isset($rnn->tagName) && $rnn->tagName == "span") {
				$realname = $rnn->nodeValue;
			}
		}
		return $realname;
	}
	
	private $URLHome = "/Account/LogOn";
	private $URLAssignments = "/Content/Student/Assignments.aspx";
	private $URLSchedule = "/Content/Student/Classes.aspx";
}
