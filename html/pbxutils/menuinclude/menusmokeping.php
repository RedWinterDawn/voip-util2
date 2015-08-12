<?php
if ($_SESSION['access'] > 2) {
  echo '<li><a href="#">SmokePing</a>
        <ul>
              <li><a href="http://smokeping.devops.jive.com/chi-1a/cgi-bin/smokeping.fcgi">CHI</a></li>
                    <li><a href="http://smokeping.devops.jive.com/atl-1a/cgi-bin/smokeping.fcgi">ATL</a></li>
                          <li><a href="http://smokeping.devops.jive.com/lax-1a/cgi-bin/smokeping.fcgi">LAX</a></li>
                                <li><a href="http://smokeping.devops.jive.com/nyc-1a/cgi-bin/smokeping.fcgi">NYC</a></li>
                                      <li><a href="http://smokeping.devops.jive.com/pvu-1a/cgi-bin/smokeping.fcgi">PVU</a></li>
                                          </ul>
                                            </li>';
}
?>
