<?php
// Initialize session
session_start();
 
function authenticate($user, $password) {
    if(empty($user) || empty($password)) return false;
 
  // Active Directory server
  $ldap_host = "ldapmaster.jiveip.net";
 
  // Active Directory DN
  $ldap_dn = "ou=Users,ou=HQ,ou=Jive,dc=jiveip,dc=net";
 
  $useruid = "uid=".$user.",ou=Users,ou=HQ,ou=Jive,dc=jiveip,dc=net";
 
  // Active Directory manager group
  $technicals = "Technical-Solutions";
  $fe = 'Field-Engineering';
  $produtils = "Tech Sol Leads";
  $IT = "cn=IT,OU=Groups,OU=HQ,ou=Jive,dc=jiveip,dc=net";
  $devops = "cn=DevOps,cn=IT,OU=Groups,OU=HQ,ou=Jive,dc=jiveip,dc=net";
  // Domain, for purposes of constructing $user
  #$ldap_usr_dom = " @college.school.edu";
  // Set access levels
  $access1 = false;
  $access2 = false;
  $access3 = false;
  $access4 = false;
 
  // connect to active directory
  $ldap = ldap_connect($ldap_host);
 
  // verify user and password
  if($bind = ldap_bind($ldap, $useruid, $password)) {
    // valid
    // check presence in groups
    
    $filter = "uid=".$user;
    $attr = array("memberof");
    $result = ldap_search($ldap, $ldap_dn, $filter, $attr) or exit("Unable to search LDAP server");
    $entries = ldap_get_entries($ldap, $result);
    ldap_unbind($ldap);
    foreach($entries[0]['memberof'] as $grps) {
      if (strpos($grps, $technicals) == true) { $access1 = true;  }
      if (strpos($grps, $fe) == true) { $access2 = true; }
      if (strpos($grps, $produtils) == true) { $access2 = true;}
      if ($grps == $IT) { $access3 = true;}
      if ($grps ==  $devops) { $access4 = true;}
    }
$access = 0;
    if ($access1 == true) { $access = 1;}
    if ($access2 == true) { $access = 2;}
    if ($access3 == true) { $access = 3;}
    if ($access4 == true) { $access = 4;} 

    }
    // check groups
  //  foreach($entries[0]['memberof'] as $grps) {
      // is manager, break loop
    //  if (strpos($grps, $ldap_manager_group)) { $access = 2; break; }
 
      // is user
      ///if (strpos($grps, $ldap_user_group)) $access = 1;
   // }
 
    if ($access != 0) {
      // establish session variables
      $_SESSION['user'] = $user;
      $_SESSION['access'] = $access;
      return true;
    } else {
      // user has no rights
      return false;
    }
 
  }
?>
