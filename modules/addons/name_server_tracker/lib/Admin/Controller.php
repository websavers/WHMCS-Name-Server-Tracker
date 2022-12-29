<?php

namespace WHMCS\Module\Addon\NameServerTracker\Admin;

use WHMCS\Database\Capsule;

/**
 * Sample Admin Area Controller
 */
class Controller {

    /**
     * Index action.
     *
     * @param array $vars Module configuration parameters
     *
     * @return string
     */
    public function index($vars) {
        // Get common module parameters
        $modulelink = $vars['modulelink']; // eg. addonmodules.php?module=addonmodule
        $version = $vars['version']; // eg. 1.0
        $LANG = $vars['_lang']; // an array of the currently loaded language variables
        
        if ($_REQUEST['success'] == 1){
          $successbox = '<div class="successbox"><strong><span class="title">Changes Saved Successfully!</span></strong><br>Your changes have been saved.</div>';
        }
    
        $shared_servers = Capsule::table('tblservers')
          ->select('id', 'hostname', 'ipaddress', 'nameserver1', 'nameserver2', 'nameserver3', 'nameserver4', 'nameserver5', 'nameserver1ip', 'nameserver2ip', 'nameserver3ip', 'nameserver4ip', 'nameserver5ip')
          ->whereNotNull('nameserver1')
          ->get()->toArray();
        $dedicated_and_vps = Capsule::table('tblhosting')
          ->leftjoin('tblproducts', 'tblhosting.packageid', '=', 'tblproducts.id')
          ->leftjoin('tblclients', 'tblhosting.userid', '=', 'tblclients.id')
          ->select('tblhosting.id', 'tblhosting.domain as hostname', 'tblhosting.dedicatedip as ipaddress', 'tblhosting.ns1 as nameserver1', 'tblhosting.ns2 as nameserver2', 'tblhosting.userid', 'tblclients.firstname', 'tblclients.lastname', 'tblclients.companyname')
          ->whereNotNull('tblhosting.ns1')
          ->where('tblproducts.type', 'server')
          ->where('tblhosting.domainstatus', '=', 'Active')
          ->where('tblhosting.ns1', '!=', 'ns1') // old default
          ->where('tblhosting.ns1', '!=', 'none') // old default
          ->where('tblhosting.ns1', 'LIKE', "%{$vars['nsdomain']}")
          ->get()->toArray();

        $nst_manual_entries = Capsule::table('mod_name_server_tracker')
          ->select('id', 'nameserver as nameserver1', 'ip as ipaddress', 'server_hostname as hostname', 'created_at as customentry')
          ->get()->toArray();

        $all_nameservers = array_merge($shared_servers, $dedicated_and_vps, $nst_manual_entries);
        
        //sort($all_nameservers, SORT_NUMERIC); //proper sorting.
        usort($all_nameservers, function($a, $b){
                if (preg_match( '/^(?:ns|NS)(\d+).*$/', $a->nameserver1, $matches )){
                  $left   = (int) $matches[1];
                }
                if (preg_match( '/^(?:ns|NS)(\d+).*$/', $b->nameserver1, $matches )){
                  $right  = (int) $matches[1];
                }
                return ($left > $right);
        });
        
        $nst_table_data = '';
        
        if (empty($all_nameservers)) $nst_table_data = "<tr class='none'><td colspan='9'>No Records Found</td></tr>";
        else{
          foreach ($all_nameservers as $entry){
            $nst_table_data .= "<tr>";
            
            if ($entry->userid){
              $itemurl = "{$GLOBALS['CONFIG']['SystemURL']}/admin/clientsservices.php?userid={$entry->userid}&id={$entry->id}";
            }
            else{
              $itemurl = "{$GLOBALS['CONFIG']['SystemURL']}/admin/configservers.php?action=manage&id={$entry->id}";
            }
            
            if ($entry->firstname){
              $clientinfo = "<a href='{$GLOBALS['CONFIG']['SystemURL']}/admin/clientssummary.php?userid={$entry->userid}'>{$entry->firstname} {$entry->lastname}</a>";
            }
            else{
              if ($entry->customentry){
                $clientinfo = "<em>System Server</em>";
                $deleteaction = "&nbsp;<a href='$modulelink&action=delete&deleteid={$entry->id}'><i class='fas fa-trash-alt' style='color:red;'></i></a>";
              }
              else{
                $clientinfo = "<em>Shared Server</em>";
                $deleteaction = "";
              }
              if (!$entry->companyname){
                $entry->companyname = "<em>{$GLOBALS['CONFIG']['CompanyName']}</em>";
              }
            }
            
            for ($i = 1; $i <= 5; $i++) {
              
              $nsnamefield = "nameserver$i";
              
              if (!$entry->$nsnamefield){
                break;
              }
              
              $nsipfield = "nameserver$i" . 'ip';
              
              if (empty($entry->$nsipfield)){
                $entry->$nsipfield = $entry->ipaddress; //assumed. Or maybe we should look it up?
              }
                
              $nst_table_data .= "<tr>
              <td>{$entry->$nsnamefield}$deleteaction</td>
              <td>{$entry->$nsipfield}</td>
              <td><a href='$itemurl'>{$entry->hostname}</a></td>
              <td class='clientid' id='{$entry->userid}'>$clientinfo</td>
              <td class='organization'>{$entry->companyname}</td>";
            
            }

            $nst_table_data .= "</tr>";
          }
        }

        return <<<EOF
$successbox
<div class="tablebg">
<table id="nst_table" class="datatable filterable" width="100%" cellspacing="1" cellpadding="3" border="0">
  <thead><tr><th>Name Server</th><th>IP</th><th>Server Hostname</th><th>Client Name</th><th>Organisation</th></tr></thead>
  <tbody>$nst_table_data</tbody>
</table>
</div>
<ul class="pager"><li class="previous disabled"><a href="#">« Previous Page</a></li><li class="next disabled"><a href="#">Next Page »</a></li></ul>
<script>
  jQuery(document).ready(function($){
    $('#nst_table').dataTable(); //enable datatables
  });
</script>
<style>
  em{ color: #ccc; }
  .col-centered{
    float: none;
    margin: 0 auto;
  }
</style>

<div class="col-md-8 col-centered">
  <h2>Add Custom Entry</h2>
  <p><em>Be sure to only add custom entries in cases where you will definitely not be adding the server as a server in WHMCS or to a customer's account. In both of those cases, the server will show here automatically.</em></p>

  <form method="post" id="add-edit-entry" action="{$GLOBALS['CONFIG']['SystemURL']}/admin/$modulelink" data-no-clear="false">
    <input type="hidden" name="action" value="create">
    <input type="hidden" name="token" value="{$GLOBALS['CONFIG']['Token']}">

    <table class="form" width="100%" cellspacing="2" cellpadding="3" border="0">
    <tbody>
      <tr>
        <td class="fieldlabel" width="30%">Name Server:</td>
        <td class="fieldarea" width="70%"><input name="nameserver" class="form-control" type="text" placeholder="ns1.mydnshost.com"></td>
      </tr>
      <tr>
        <td class="fieldlabel" width="30%">IP</td>
        <td class="fieldarea" width="70%"><input name="ip" class="form-control" type="text" placeholder="1.1.1.1"></td>
      </tr>
      <tr>
        <td class="fieldlabel" width="30%">Server Hostname</td>
        <td class="fieldarea" width="70%"><input name="server_hostname" class="form-control" type="text" placeholder="vps.myserver.com"></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td><input type="submit" value="Add New" class="btn btn-primary"></td>
      </tr>
    </tbody>
    </table>
  </form>
</div>
EOF;
    }
    
    /**
     * Create Record action.
     *
     * @param array $vars Module configuration parameters
     *
     * @return string
     */
    public function create($vars) {
      
      $this->validate_input(array($_REQUEST['nameserver'], $_REQUEST['ip'], $_REQUEST['server_hostname']));

      $id = Capsule::table('mod_name_server_tracker')->insertGetId(array(
          'nameserver'      => $_REQUEST['nameserver'], 
          'ip'              => $_REQUEST['ip'],
          'server_hostname' => $_REQUEST['server_hostname'],
          'created_at'  => \Carbon\Carbon::now(),
      ));

      if ($id == null || $id == 0){ //show error
        throw new Exception( 'There was an error inserting to the database.' );
      }
      else{
        header("Location: {$GLOBALS['CONFIG']['SystemURL']}/admin/{$vars[modulelink]}&success=1");
      }
      
    }
    
    /**
     * Delete action.
     *
     * @param array $vars Module configuration parameters
     *
     * @return string or redirect
     */
    public function delete($vars){
      
      $this->validate_input(array($_REQUEST['deleteid']));
      
      $status = Capsule::table('mod_name_server_tracker')->where('id', $_REQUEST['deleteid'])->delete();
      
      if ($status == null){ //show error
        throw new Exception("There was an error deleting ID {$_REQUEST[deleteid]}.");
      }
      else{
        header("Location: {$GLOBALS['CONFIG']['SystemURL']}/admin/{$vars[modulelink]}&success=1");
      }
      
    }
    
    private function validate_input(array $input){
      foreach ($input as $var){
        if (empty($var)){
          print("<pre style='height:300px;width:100%;overflow:scroll;'>"); var_dump($vars); print("</pre>");
          throw new Exception( 'Error validating parameters.' );
        }
      }
    }

}
