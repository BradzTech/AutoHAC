<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=0.75">
    <title>AutoHAC</title>

    <!-- The following resources are taking directly from original Home Access Center. -->
    <link href="{{ $assetUrl }}/HomeAccess/Stylesheets/Themes/custom-theme/jquery?v=uqv12MpqsgUZl2tyOuQEmZP7fbSE23Yz-see4j7kRtc1" rel="Stylesheet" type="text/css" /> 

    <link href="{{ $assetUrl }}/HomeAccess/Stylesheets/Trirand/ui.jqgrid.css" rel="Stylesheet" type="text/css" />

    <link href="{{ $assetUrl }}/HomeAccess/Stylesheets/Common/CrossSite.css" rel="Stylesheet" type="text/css" />
    <link href="{{ $assetUrl }}/HomeAccess/Stylesheets/Site.css" rel="Stylesheet" type="text/css" />
    <link rel="SHORTCUT ICON" href="{{ $assetUrl }}/HomeAccess/Media/Themes/Base/Common/favicon.ico"/>

    <link href="{{ $assetUrl }}/HomeAccess/Stylesheets/Common/Combobox.css" rel="Stylesheet" type="text/css" />
    <link href="{{ $assetUrl }}/HomeAccess/Stylesheets/Common/Navigation.css" rel="Stylesheet" type="text/css" />
    <link href="{{ $assetUrl }}/HomeAccess/Stylesheets/Common/font-awesome.css" rel="Stylesheet" type="text/css" />

    <script src="{{ $assetUrl }}/HomeAccess/Scripts/libs/jquery?v=VyihCQeLYprLqH1GJIGx132sc3WZ99r6OVB7axY5Zho1" type="text/javascript"></script>

    <script src="{{ $assetUrl }}/HomeAccess/CommonResource/GetEmbeddedScript?file=SungardCommon" type="text/javascript"></script>
    <script src="{{ $assetUrl }}/HomeAccess/CommonResource/GetEmbeddedScript?file=Combobox" type="text/javascript"></script>

    <script src="{{ $assetUrl }}/HomeAccess/Scripts/libs/modernizr-1.7.min.js" type="text/javascript"></script>
    <script src="{{ $assetUrl }}/HomeAccess/Scripts/Trirand/ui.multiselect.js" type="text/javascript" ></script>
    <script src="{{ $assetUrl }}/HomeAccess/Scripts/Trirand/i18n/grid.locale-en.js" type="text/javascript"></script>
    <script src="{{ $assetUrl }}/HomeAccess/Scripts/Trirand/jquery.jqGrid.min.js" type="text/javascript"></script>
    <script src="{{ $assetUrl }}/HomeAccess/Scripts/Common/HAC.js" type="text/javascript"></script>
    <script type="text/javascript">
        $(function () {
            SunGard.Common.Init();
            SunGard.Hac.Init('{{ $assetUrl }}/HomeAccess/');
        });
    </script>
        
    <link href="{{ $assetUrl }}/HomeAccess/Stylesheets/Account/LogOn.css" rel="Stylesheet" type="text/css" />
    <link href="{{ $assetUrl }}/HomeAccess/Stylesheets/Frame/_Banner.css" rel="Stylesheet" type="text/css" />
    <script type="text/javascript" src="{{ $assetUrl }}/HomeAccess/Scripts/libs/Validation?v=7oOBVsLMhOKm8e8UVJudeUaEBN7vJx0q_STr2y4BeuA1"></script>
    <style>
        .sg-banner-logo-color {
	        background-color: #0071ba ;
	        color: #ffffff 
        }
        .sg-banner-text-color {
            color: #ffffff 
        }
        body {
	        min-width: 400px !important;
        }
        subspan {
	        color: #999;
        }
    </style>
</head>
<body>
    <div class="sg-banner sg-banner-default-logo"> 
        <div class="sg-banner-left sg-banner-left-default-logo">
            <div class="sg-banner-info-container">
                <span class="sg-banner-text sg-banner-default-text-color">Auto Home Access Center</span>
            </div>
        </div>
        <!--<div class="sg-banner-right sg-banner-right-default-logo"></div>-->
    </div>

	<form action="/autohac" method="post"><div class="sg-container" id="SignInSectionContainer">
		{{ csrf_field() }}
        <div class="sg-header">
            <div class="sg-header-heading"><img src="{{ $assetUrl }}/HomeAccess/Media/images/Banner/logon-logout-small.png" />Login</div>
        </div>
        <div class="sg-content sg-logon">
        @if (isset($phoneNum))
            Thanks! You are almost finished with the registration process. You should recieve a message at <strong>{{ $phoneNum }}</strong>. Just respond with this verification number to complete the registration:
            <h2 style="text-align: center;">{{ $signupCode }}</h2>
            If that's not your number, please re-signup.<br>If you do not get the text and you entered a valid number, then send a text to Brad (484-635-0214) for assistance.
        @elseif (isset($newuser) || isset($activeAccount))
            <b>Welcome {{ $uname }}
	        @if (isset($realname))
		        ({{$realname}})
	        @endif
            !</b>
			<input type="hidden" name="UserName" value="{{ $uname }}" />
		    <input type="hidden" name="Password" value="{{ $pword }}" />
		    <input type="hidden" name="Distri" value="{{ $distri }}" />
            @if (isset($newuser))
	            Use the form below to complete registration.
	            <div style="margin-top: 8px;">
		            Do you have Verizon?    
		            <input type="radio" name="verizonYN" value="true"> Yes    
					<input type="radio" name="verizonYN" value="false"> No 
	            </div>
	            <div id="verizonDiv" style="margin-top: 8px;">
		            Great, you can SMS with AutoHAC!<br><subspan>(Note: SMS is slower and may split up messages. For the best experience, <a onclick="kikNo();" href="#">try Telegram</a>.)</subspan><br>
	                <label class="sg-logon-left" for="LogOnDetails_PN">Cell phone #:</label>
	                <input class="sg-logon-right" data-val="true" data-val-required="The Phone Number: field is required." id="LogOnDetails_pn" name="PhoneNum" type="text" />
		            <span class="field-validation-valid sg-logon-validation" style="color:black;">Please enter your phone number, no hyphens/spaces.</span><span class="field-validation-valid sg-logon-validation" style="color:black;">Example: 4841234567</span>
		            <button class="sg-button sg-logon-button" style="margin-top: 12px;">Sign up!</button>
	            </div>
	            <div id="telegramDiv" style="margin-top: 8px;">
					<p>Note: Kik is being phased out since it is blocked on BYOD.</p>
					<h3 style="text-align: center;">Recommended: Telegram</h3>
					Telegram is a free, lightweight messaging app-- the closest thing to an "official" app for AutoHAC. It can be used in home/school WiFi, and if you have a data plan, it won't use much. All you need to sign up is a phone number; no need to make yet another account.
		            <ol>
			            <li>Download Telegram from the <a href="https://telegram.org/dl/ios">App Store</a> or <a href="https://telegram.org/dl/android">Google Play</a>.</li>
			            <li>Once you're signed up, <a href="http://telegram.me/AutoHACbot">click here from your phone</a> OR tap the new chat button in the upper-right corner and enter <strong>AutoHACbot</strong>.</li>
			            <li>Send a message with this signup verification code:</li>
			            <h2 style="text-align: center;">{{ $signupCode }}</h2>
		            </ol>
	            </div>
				<script>
					var verizonDiv = $('#verizonDiv');
					var telegramDiv = $('#telegramDiv');
					var divs = [verizonDiv, telegramDiv];
					function hideDivs() {
						for (i in divs) {
							divs[i].hide();
						}
					}
					hideDivs();
					$('input[type=radio][name=verizonYN]').change(function() {
						hideDivs();
						if (this.value == 'true') {
							verizonDiv.show();
						} else {
							telegramDiv.show();
						}
					});
				</script>
            @else
	        	You are currently signed up using <strong>{{ $activeAccount }}</strong>. To deactivate your account, click the button below. To change your contact information, please deactivate your account, then reactivate it with the new information.
            @endif
        @elseif (isset($privacy))
            In order for AutoHAC to function properly, I must collect some personally identifiable information from you, including <u>your school username/password and all of your grades</u>. <b>I will NEVER give this information to anyone under any circumstances</b>, nor will I use it for any purpose other than debugging the program without your express consent.<br><br>Unfortunately, that's all the comfort I can give, as this program is impossible without collecting such information. For a more detailed privacy policy regarding my website as a whole, <a href='http://www.bradztech.com/blog/privacy/'>click here</a>. Please close this page and do not sign up if you do not agree to these terms.
        @else
            <div>
            @if (isset($parentaccount))
            	<strong>Sorry {{ $realname }}! AutoHAC currently does not support parent login. Please the username and password you would use to log in to a school computer!</strong><br>
            @endif
			Please enter your account information for Home Access Center (EHS: School Computer Username / Password).</div>            
	        <div>
	<label class="sg-logon-left" for="Distri">Select a District:</label><select class="sg-logon-right sg-combobox" data-val="true" data-val-number="The field Select a District: must be a number." data-val-required="The Select a District: field is required." id="Distri" name="Distri">
			@foreach ($districts as $district)
				<option value="{{ $district->id }}">{{ $district->name }}</option>
			@endforeach
			</select><span class="field-validation-valid sg-logon-validation" data-valmsg-for="Database" data-valmsg-replace="true"></span></div>
	            <div>
	            <label class="sg-logon-left" for="LogOnDetails_UserName">User Name:</label> 
	            <input class="sg-logon-right" data-val="true" data-val-required="The User Name: field is required." id="LogOnDetails_UserName" name="UserName" type="text" /> 
	            <span class="field-validation-valid sg-logon-validation" data-valmsg-for="UserName" data-valmsg-replace="true"></span>
	        </div>
	        <div>
	            <label class="sg-logon-left" for="LogOnDetails_Password">Password:</label> 
	            <input class="sg-logon-right" data-val="true" data-val-required="The Password: field is required." id="LogOnDetails_Password" name="Password" type="password" autocomplete="off" /> 
	            <span class="field-validation-valid sg-logon-validation" data-valmsg-for="Password" data-valmsg-replace="true"></span>
	        </div>
        @endif
        @if (isset($activeAccount))
            <input type="hidden" name="deactivate" value="true" />
            <button class="sg-button sg-logon-button">Deactivate</button>
        @elseif (isset($privacy))
            <button class="sg-button sg-logon-button">I Agree</button>
        @elseif (isset($homepage))
            <button class="sg-button sg-logon-button">Login</button>
        @endif
        <div>
	        @if (isset($wrongpword))
	        	<a><b>Username/Password incorrect!</b></a>
	        @endif
	        @if (isset($homepage))
	        	<span style="color:#999"><br>I agree to the <a href="/autohac/privacy" style="color:#99F">privacy policy</a>.</span>
	        @endif
        </div>
    </div>
    @if (isset($distri))
		<input type="hidden" name="Distri" value="{{ $distri }}">
    @endif
	</form>
    <script>
        $(function() {
            // Build the jQuery UI buttons
            $('.sg-buttonset').buttonset();
            $('.sg-button').button();
        })
    </script>
</body>
</html>
