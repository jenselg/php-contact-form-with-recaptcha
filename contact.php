<?php

    // BASIC CONTACT FORM WITH RECAPTCHA VERIFICATION
    // USAGE INSTRUCTIONS:
    // 1. form action needs to point here, i.e. action="contact.php"
    // 2. form inputs' name need to match variable declarations here, i.e. $name = $_POST['input-name'] and name="input-name"
    // 3. make sure variables match in the form vars section and message var section
    // 4. make sure to have a valid captcha secret key defined here
    // 5. make sure you have the following line in the html form along with the site key: <div class="g-recaptcha" data-sitekey="SITEKEY-HERE"></div>
    // 6. make sure you have the follow line before the closing head tag: <script src='https://www.google.com/recaptcha/api.js'></script>
    // 7. If for some reason the script fails, try $_POST instead of $_REQUEST for the variables defined in this script

    // mail vars, edit as needed - what email address should the contact form send to, and what the subject line is
    // can take multiple email address, just separate by comma
    $receiving_email_address = 'my@email.com';
    $receiving_email_subject = 'CONTACT FORM SUBMISSION';

    // page vars, edit as needed - pages where the browser should redirect to upon submission success or failure
    $page_send_success = 'form-success.html';
    $page_send_fail = 'form-fail.html';

    // form vars, edit as needed - variables here need to match whatever is on the html form
    // we use stripslashes() here so that submitted data doesn't break any formatting on the message body, i.e. \n or \r
    // when building your html forms, the name="" attribute in the input is whatever the $_REQUEST[''] is
    $email = stripslashes($_REQUEST['email']);
    $phone = stripslashes($_REQUEST['phone']);
    $special_message = stripslashes($_REQUEST['special_message']);

    // message var, edit as needed - what the email body should look like, so include the form vars as well
    // note: dont use $msg variable anywhere else
    $msg = "
    CONTACT FORM SUBMISSION: \n
    ---------------------------------------------------------------------------- \n
    Email Address: $email \n
    Telephone Number: $phone \n
    Special Message: $special_message \n
    ---------------------------------------------------------------------------- \n
    ";

    // captcha vars, edit as needed - get this from google - captcha_response and verification_url wont need editing, just the secret key
    $captcha_secret = 'CAPTCHA-SECRET-KEY-HERE';
    $captcha_response = $_REQUEST['g-recaptcha-response'];
    $captcha_verification_url = 'https://www.google.com/recaptcha/api/siteverify';

    // *************************************************************************************************
    // ** START SEND LOGIC - NO NEED FOR EDITING FROM HERE ON, UNLESS GOOGLE DECIDES TO CHANGE THINGS **
    // *************************************************************************************************

    // BUILD OUR QUERY AND USE CURL
    $post_data = "secret=".$captcha_secret."&response=".$captcha_response."&remoteip=".$_SERVER['REMOTE_ADDR'];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $captcha_verification_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER,
    array('Content-Type: application/x-www-form-urlencoded; charset=utf-8',
    'Content-Length: ' . strlen($post_data)));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    $googresp = curl_exec($ch);
    $decgoogresp = json_decode($googresp);
    curl_close($ch);

    // CONDITIONAL LOGIC BASED ON GOOGLE'S RESPONSE, EDIT IF YOU NEED TO DEBUG
    if ($decgoogresp->success == true) {
        // this means the google recaptcha was a success, so we go ahead and send mail and redirect to whatever page was defined above
        mail($receiving_email_address,$receiving_email_subject,$msg,"From:$email");
        header("Location: ".$page_send_success);
    } else {
        // recaptcha failed for some reason
        header("Location: ".$page_send_fail);
        // uncomment the line below to see google's response, and comment the line above, if the script is failing
        // print_r($decgoogresp);
    }

?>
