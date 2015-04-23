<?php
## active agi servers
exec('sudo ssh root@10.101.8.1 \'grep -F "update delete" agi.srv.txt | cut -d " " -f 10 | cut -d "." -f 1 | cut -d "i" -f 2\'', $output, $exitcode);
echo 'AGI Servers:<br>';
print_r($output);
## agi versions
exec('sudo ssh root@10.101.8.1 \'salt "agi*.c1.*" cmd.run "tail -n 1 /root/jive.deploy.log | cut -d \'-\' -f8"\'', $output1, $exitcode);
echo '<br>AGI Versions:<br>';
print_r($output1);
## active wa servers
exec('sudo ssh root@10.101.8.1 \'salt "wb*" cmd.run "grep -A 2 mobility /etc/httpd/conf/workers.properties | grep -o wa[0-9]; grep -A 2 partner-portal /etc/httpd/conf/workers.properties | grep -o wa[0-9]"\'', $output2, $exitcode);
echo '<br>WA Servers<br>';
print_r($output2);
## wa versions
exec('sudo ssh root@10.101.8.1 \'salt "wa*" cmd.run "tail -n 1 /root/jive.deploy.log | cut -d \'/\' -f12"\'', $output2, $exitcode);
echo '<br>WA Versions<br>';
print_r($output2);
