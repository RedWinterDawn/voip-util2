<?php
if ($_SESSION['access'] > 2) {
  echo '<li><a href="#">CTC</a>
        <ul>
              <li><a href="http://ctc.devops.jive.com">Inbound Controller</a></li>
                    <li><a href="http://ctc.devops.jive.com/edit_number.php">Manage Numbers</a></li>
                        </ul>
                            </li>';
}
?>
