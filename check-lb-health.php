#!/usr/bin/php
<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

// Commandline arguments are used to set the correct parameters
$region = $argv[1];
$elbName = $argv[2];
$warningLevel = $argv[3];
$criticalLevel = $argv[4];

// Settings up the AWS API connection
use Aws\Common\Aws;
$aws = Aws::factory( [
    'key'    => $aws_auth_key,
    'secret' => $aws_auth_secret,
    'region' => $region
  ] 
);

// Getting the
$client = $aws->get('ElasticLoadBalancing');
$result = $client->describeInstanceHealth(['LoadBalancerName' => $elbName]);

// Count the amount of nodes, used to define the warning or crit level later on
$totalHosts = count($result['InstanceStates']);

// Parsing the information
echo "Total Hosts in ELB: " . $totalHosts . " : ";
$unHealthyHosts = 0;
foreach( $result['InstanceStates'] as $key => $val )
{
  echo "{Instance [" . $val['InstanceId'] . "] is in state [" . $val['State']  . "]} ";
  if ( $val['State'] != 'InService' ) { 
    $unHealthyHosts += 1;
  }
}
echo "\n";

if ( $unHealthyHosts >= $criticalLevel ) {
  exit(2);
} elseif ( $unHealthyHosts >= $warningLevel ) {
  exit(1);
}

?>
