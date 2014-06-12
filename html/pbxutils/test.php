<?php
exec('bash -c "exec nohup /root/loadMetrics.py > /dev/null 2>&1 &"');
echo "Started load metrics update in the background. You don't need to wait for it.";

?>
