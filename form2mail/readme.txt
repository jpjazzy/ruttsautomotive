Step 1. CHANGE the basic settings in the file 'config.php'

You can use this script to submit your forms or to receive orders by email.
You can find two example forms in this zip.

The built-in captcha works by changing an image src file using Javascript. (see the examples - look for captcha-img and captcha-response fields)

If you want to make some fields required, you can set the REQUIRED_FIELDS constant, in the config.php file

If you are asking for a name and an email address in your form, you can name the input fields "name" and "email". If you do this, the message will apear to come from that email address and you can simply click the reply button to answer it.

To block an IP, simply add it to the blockip.txt text file.
CHMOD 755 the blockip.txt file (run "CHMOD 755 blockip.txt" in any FTP client, without the double quotes)
This is needed because the script tries to block the IP that tried to hack it

If you have a multiple selection box or multiple checkboxes, you MUST name the multiple list box or checkbox as "name[]" instead of just "name" (add the [] after the actual name)
you must also add "multiple" at the end of the tag like this: <select name="myselectname[]" multiple>
you have to do the same with checkboxes


Thank you for downloading!

If you need support, please contact us directly at http://www.web4future.com/contact.htm
