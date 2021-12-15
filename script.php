<?php
require 'vendor/autoload.php';

use Iodev\Whois\Factory;

// Config:
$DOMAINTOPOPULATE = 'sedical';
$SENDEMAILALERTTO = 'EMAILTOSENDALERT';

// Creating default configured client
$whois = Factory::get()->createWhois();

function populate($name){
    $name = trim(strtolower($name));

    $populatedNames = [$name];
    $charArr = str_split($name);
    //Duplicate all chars:
    for($i=0; $i<strlen($name)-1; $i++){
        $charToRepeat = $charArr[$i];
        $populated = $charArr;
        array_splice( $populated, $i, 0, $charToRepeat );
        $name = implode($populated);
        $populatedNames[] = $name;
    }
    //Change letters with similar numbers chars:
    for($i=0; $i<strlen($name)-1; $i++){
        if($name[$i] === 'i'){
            $populatedNames[] = substr_replace($name, '1', $i, -1);
        }
        if($name[$i] === 'l'){
            $populatedNames[] = substr_replace($name, '1', $i, -1);
        }
        if($name[$i] === 's'){
            $populatedNames[] = substr_replace($name, '3', $i, -1);
        }
        if($name[$i] === 'o'){
            $populatedNames[] = substr_replace($name, '0', $i, -1);
        }
    }

    return $populatedNames;
}


// TLD list from: https://gist.github.com/wridgers/1968843
// Added own TLD: .eus
$TLDs = ['aero', 'asia', 'biz', 'cat', 'com', 'coop', 'info', 'int', 'jobs', 'mobi', 'museum', 'name',
    'net', 'org', 'pro', 'tel', 'travel', 'xxx', 'edu', 'gov', 'mil', 'ac', 'ad', 'ae', 'af', 'ag',
    'ai', 'al', 'am', 'an', 'ao', 'aq', 'ar', 'as', 'at', 'au', 'aw', 'ax', 'az', 'ba', 'bb', 'bd',
    'be', 'bf', 'bg', 'bh', 'bi', 'bj', 'bm', 'bn', 'bo', 'br', 'bs', 'bt', 'bv', 'bw', 'by', 'bz',
    'ca', 'cc', 'cd', 'cf', 'cg', 'ch', 'ci', 'ck', 'cl', 'cm', 'cn', 'co', 'cr', 'cs', 'cu', 'cv',
    'cx', 'cy', 'cz', 'dd', 'de', 'dj', 'dk', 'dm', 'do', 'dz', 'ec', 'ee', 'eg', 'eh', 'er', 'es',
    'et', 'eu', 'eus','fi', 'fj', 'fk', 'fm', 'fo', 'fr', 'ga', 'gb', 'gd', 'ge', 'gf', 'gg', 'gh',
    'gi', 'gl', 'gm', 'gn', 'gp', 'gq', 'gr', 'gs', 'gt', 'gu', 'gw', 'gy', 'hk', 'hm', 'hn', 'hr',
    'ht', 'hu', 'id', 'ie', 'il', 'im', 'in', 'io', 'iq', 'ir', 'is', 'it', 'je', 'jm', 'jo', 'jp',
    'ke', 'kg', 'kh', 'ki', 'km', 'kn', 'kp', 'kr', 'kw', 'ky', 'kz','la', 'lb', 'lc', 'li', 'lk',
    'lr', 'ls', 'lt', 'lu', 'lv', 'ly', 'ma', 'mc', 'md', 'me', 'mg', 'mh', 'mk', 'ml', 'mm', 'mn',
    'mo', 'mp', 'mq', 'mr', 'ms', 'mt', 'mu', 'mv', 'mw', 'mx', 'my', 'mz', 'na', 'nc', 'ne', 'nf',
    'ng', 'ni', 'nl', 'no', 'np', 'nr', 'nu', 'nz', 'om', 'pa', 'pe', 'pf', 'pg', 'ph', 'pk', 'pl',
    'pm', 'pn', 'pr', 'ps', 'pt', 'pw', 'py', 'qa', 're', 'ro', 'rs', 'ru', 'rw', 'sa', 'sb', 'sc',
    'sd', 'se', 'sg', 'sh', 'si', 'sj', 'sk', 'sl', 'sm', 'sn', 'so', 'sr', 'ss', 'st', 'su', 'sv',
    'sy', 'sz', 'tc', 'td', 'tf', 'tg', 'th', 'tj', 'tk', 'tl', 'tm', 'tn', 'to', 'tp', 'tr', 'tt',
    'tv', 'tw', 'tz', 'ua', 'ug', 'uk', 'us', 'uy', 'uz', 'va', 'vc', 've', 'vg', 'vi', 'vn', 'vu',
    'wf', 'ws', 'ye', 'yt', 'yu', 'za', 'zm', 'zw'];

$populatedDomainNames = populate($DOMAINTOPOPULATE);

$emailDomains = [];
file_put_contents("log.txt", "");//Empty before start file:
foreach( $populatedDomainNames as $domainName){
    foreach ($TLDs as $tldExt) {
        $checkingDomain = trim(strtolower($domainName . "." .$tldExt));

        // Checking availability
        $available = 0;
        try {
            $available = $whois->isDomainAvailable($checkingDomain);
            $log = $checkingDomain . " | " . $available ."\n";
        }catch (Exception $e) {
            $available = 2;
            $log = $checkingDomain . " | " . $available ."\n";
        }

        // write into log.txt:
        file_put_contents('log.txt', $log, FILE_APPEND);

        // check if is an alert:
        if($available == 0) {
            $noAlertArray = file('./noalert.txt', FILE_IGNORE_NEW_LINES);

            if (in_array($checkingDomain,$noAlertArray)) {
                // Ya se encuentra en el array, no hacer nada.
            }else{
                $emailDomains[] = $checkingDomain;
                // Add for no alert more: (in linux \r\n can be replaced by \n)
                //print('Alert email for: '. $checkingDomain ."\n");
                file_put_contents('noalert.txt', $checkingDomain . "\r\n", FILE_APPEND);
            }
        }
    }
}
if(count($emailDomains) > 0){
    $emailText = "The domains: \r\n";
    if(count($emailDomains) === 1){
        $emailText = "The domain: \r\n";
    }
    foreach($emailDomains as $domainToAlert){
        $emailText .= $domainToAlert . "\r\n";
    }
    mail($SENDEMAILALERTTO,"Similar domain alert", $emailText . " \n\n has been registered.");
}
