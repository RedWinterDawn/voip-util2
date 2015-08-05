<?php
if ($_SESSION['access'] > 2) {
  echo ' <li><a href="https://10.132.0.224/xymon/">Xymon</a>
        <ul>
              <li><a href="https://10.132.0.224/xymon/">OPS (All Sites)</a></li>
                    <li><a href="https://162.250.62.224/xymon/">ATL</a></li>
                          <li><a href="https://199.36.251.224/xymon/">DFW</a></li>
                                <li><a href="https://162.250.63.224/xymon/">GEG</a></li>
                                      <li><a href="https://162.250.60.224/xymon/">LAX</a></li>
                                            <li><a href="https://199.87.122.224/xymon/">LON</a></li>
                                                  <li><a href="https://162.250.61.224/xymon/">NYC</a></li>
                                                        <li><a href="https://199.87.123.224/xymon/">PVU</a></li>
                                                            </ul>
                                                              </li>';
}
?>
