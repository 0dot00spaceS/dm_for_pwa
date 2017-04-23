<?php
/**
 * /_includes/email/send-new-password.inc.php
 *
 * This file is part of DomainMOD, an open source domain and internet asset manager.
 * Copyright (c) 2010-2017 Greg Chetcuti <greg@chetcuti.com>
 *
 * Project: http://domainmod.org   Author: http://chetcuti.com
 *
 * DomainMOD is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
 * version.
 *
 * DomainMOD is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with DomainMOD. If not, see
 * http://www.gnu.org/licenses/.
 *
 */
?>
<?php
$email = new DomainMOD\Email();

list($full_url, $from_address, $null_variable, $use_smtp) = $email->getSettings($dbcon);

$to_address = $email_address;
$from_name = $software_title;

$subject = "Your " . $software_title . " Password has been Reset";
$headline = "Your " . $software_title . " Password has been Reset";

$headers = '';
$headers .= 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
$headers .= 'From: "' . $software_title . '" <' . $from_address . ">\r\n";
$headers .= 'Return-Path: ' . $from_address . "\r\n";
$headers .= 'Reply-to: ' . $from_address . "\r\n";
$version = phpversion();
$headers .= 'X-Mailer: PHP/' . $version . "\r\n";

$message_html .= "
<html>
<head><title>" . $headline . "</title></head>
<body bgcolor=\"#FFFFFF\">
<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" bgcolor=\"#FFFFFF\">
    <tr>
        <td width=\"100%\" bgcolor=\"#FFFFFF\">

            <table width=\"575\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" bgcolor=\"#FFFFFF\"
                bordercolor=\"#FFFFFF\">
                <tr>
                    <td width=\"4%\" valign=\"top\" align=\"left\">&nbsp;</td>
                    <td width=\"92%\"><font color=\"#000000\" size=\"2\" face=\"Verdana, Arial, Helvetica,
                        sans-serif\">";
$message_html .= "<a title=\"" . $software_title . "\" href=\"" . $full_url . "/\"><img alt=\"" .
    $software_title . "\" border=\"0\" src=\"" . $full_url . "/images/logo.png\"></a><BR><BR>";
$message_html .= "Your password has been reset and you can find it below. The next ";
$message_html .= "time you login you should change your password to something that ";
$message_html .= "will be easy for you to remember, but still hard for someone ";
$message_html .= "else to guess.<BR>";
$message_html .= "<BR>";
$message_html .= "URL: <a title=\"DomainMOD\" target=\"_blank\" href=\"" . $full_url . "/\">" .
    $full_url . "/</a><BR>";
$message_html .= "<BR>";
$message_html .= 'Your Username: ' . $username . '<BR>';
$message_html .= 'Your New Password: ' . $new_password . '<BR>';
$message_html .= "<BR>";
$message_html .= "Best Regards,<BR>";
$message_html .= "<BR>";
$message_html .= "Greg Chetcuti<BR>";
$message_html .= "<a target=\"_blank\"
                            href=\"mailto:greg@domainmod.org\">greg@domainmod.org</a><BR>";
$message_html .= "<BR>";
$message_html .= "</font>
                    </td>
                    <td width=\"4%\" valign=\"top\" align=\"left\">&nbsp;</td>
                </tr>
            </table>

            <table width=\"575\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" bgcolor=\"#FFFFFF\"
                bordercolor=\"#FFFFFF\">
                <tr>
                    <td width=\"4%\" valign=\"top\" align=\"left\">&nbsp;</td>
                    <td width=\"92%\"><font color=\"#000000\" size=\"2\" face=\"Verdana, Arial, Helvetica,
                        sans-serif\">";
$message_html .= "<hr width=\"100%\" size=\"2\" noshade>";
$message_html .= "You've received this notification because someone requested a password reset for
                            your ";
$message_html .= $software_title . " account.<BR>";
$message_html .= "<BR>";
$message_html .= "If you did not request this yourself, it sounds like somebody might be trying to
                            gain access ";
$message_html .= "to your account. This might be a good time to reset your password again just to be
                            safe. ";
$message_html .= "<a target=\"_blank\" href=\"" . $full_url . "/reset.php\">" . $full_url . "/reset.php</a>";
$message_html .= "<BR></font>
                    </td>
                    <td width=\"4%\" valign=\"top\" align=\"left\">&nbsp;
                    </td>
                </tr>
            </table>

        </td>
    </tr>
</table>
</body>
</html>";

$message_text .= $headline . "\n\n";
$message_text .= "Your password has been reset and you can find it below. The next time you login you should change your password to something that will be easy for you to remember, but still hard for someone  else to guess.\n\n";
$message_text .= "URL: " . $full_url . "\n";
$message_text .= "\n";
$message_text .= "Your Username: " . $username . "\n";
$message_text .= "Your New Password: " . $new_password . "\n";
$message_text .= "\n";
$message_text .= "Best Regards,\n";
$message_text .= "\n";
$message_text .= "Greg Chetcuti\n";
$message_text .= "greg@domainmod.org\n";
$message_text .= "\n---\n\n";
$message_text .= "You've received this notification because someone requested a password reset for your " . $software_title . " account.\n";
$message_text .= "\n";
$message_text .= "If you did not request this yourself, it sounds like somebody might be trying to gain access to your account. This might be a good time to reset your password again just to be safe. " . $full_url . "/reset.php";

if ($use_smtp != '1') {

    mail($to_address, $subject, $message_html, $headers, '-f' . $from_address);

} else {

    $smtp = new DomainMOD\Smtp();
    $smtp->send($dbcon, $from_address, $to_address, $first_name . ' ' . $last_name, $subject, $message_html, $message_text);

}
