<?php

require 'PHPMailerAutoload.php';



      $to = 'nrnane@gmail.com';
      $subject = 'Email format Test';
      $message = "<div style='background: url(https://ecogymworldwide.com/Images/Regular_NewsletterDesignHeader_bg.jpg) no-repeat;
                                                background-size: cover;width: 844px; min-height: 542px; margin: 0 auto; font-family: SegoeUI, Arial !important;
                                                padding: 270px 0px 0px; font-size: 13px; color: #717171; line-height: 20px;'>
                                                <div style='background: url(https://ecogymworldwide.com/Images/Regular_NewsletterDesignFooter_bg.jpg) bottom no-repeat;background-size: cover;padding: 0 30px 200px;'>
                                                <!--<span style='font-size: 22px !important;'>[CANCEL APPROVAL NOTICE]</span><br/>-->
                                                <span style='font-size: 18px !important;'>Dear @MEMBERNAME</span>
                                                <p class='nl-p'>
                                                    After review of your account, we are happy to let you know that your request to CANCEL your account has been approved.
                                                </p>
                                                <p class='nl-p'>
                                                    We appreciate your business and hope that you spread the word about our brand and vision to help our environment. Please continue to do your part to improve your health and the health of our world!
                                                </p>
                                                <!--<p class='nl-p'>
                                                    Recently you requested a <span style='color: #c83535 !important; font-size: 17px !important;'>cancellation</span> to your
                                                    account for membership.
                                                    We greatly appreciate your business and have reviewed your previous and current
                                                    account status in accordance with your membership agreement to make a determination
                                                    on your request.
                                                </p>
                                                <p style=''>
                                                    Please see the information below regarding your request.</p>
                                                <table width='600' border='0' cellspacing='0' cellpadding='0' style='font-size: 16px !important;'>
                                                    <tr>
                                                        <td height='30' style='color: #464646 !important;'>
                                                            Member ID:
                                                        </td>
                                                        <td style=''>
                                                            @MEMBERID<br>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td height='30' style='color: #464646 !important;'>
                                                            Member Name:
                                                        </td>
                                                        <td style=''>
                                                            @MEMBERNAME
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td height='30' style='color: #464646 !important;'>
                                                            Request Submission Date:
                                                        </td>
                                                        <td style=''>
                                                            @REQSUBMISSIONDATE
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td height='30' style='color: #464646 !important;'>
                                                            Reason for Request:
                                                        </td>
                                                        <td style=''>
                                                            @REASON
                                                        </td>
                                                    </tr><tr>
                                                        <td height='30' style='color: #464646 !important;'>
                                                            Comments from customer service:
                                                        </td>
                                                        <td style=''>
                                                            @COMMENTS
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td height='30' style='color: #464646 !important;'>
                                                            Approval Status:
                                                        </td>
                                                        <td style=''>
                                                            @STATUS
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td height='30' style='color: #464646 !important;'>
                                                            Date of Approval
                                                        </td>
                                                        <td style=''>
                                                            @CANCELDATE
                                                        </td>
                                                    </tr>
                                                </table>-->
                                                <br />
                                                <span style='font-size: 16px !important;'><a href='#'>Click here to login</a></span>
                                                 <br />
                                                 <br />
                                                <span style='font-size: 18px !important;'>Healthy Regards,</span><br />
                                                <span style='font-size: 18px !important;'>Michael Benso and Chris Gellings</span><br />
                                                <span style='font-size: 18px !important;'>Founders of Eco Gym</span><br />
                                                <!--<span>Build No: </span>-->
                                                </div>
                                            </div>";
       $mail = new PHPMailer(); // create a new object
       $mail->IsSMTP(); // enable SMTP
       $mail->SMTPDebug = 0; // debugging: 1 = errors and messages, 2 = messages only
       $mail->SMTPAuth = true; // authentication enabled
       //$mail->SMTPSecure = 'tls'; // secure transfer enabled REQUIRED for GMail
       $mail->Host = "mail.vaazu.com";
       $mail->Port = 25; // or 587
       $mail->IsHTML(true);
       $mail->Username = "support@vaazu.com";
       $mail->Password = "Member123";
       /*$mail->Username = "vaazuemail@gmail.com";
       $mail->Password = "Vaazu@2012";*/
       /*$mail->From = $femail;
       $mail->FromName = $fname;*/
       $mail->setFrom("webmaster@vaazu.com","Server");
       $mail->Subject = $subject;
       $mail->Body = $message;
       $mail->AddAddress($to);

        //$header.= " \r\n";

        $mail->Send();

       //$retval = mail ($to,$subject,$message,$header);



 ?>
