<?php

require_once('../lib/Phirehose.php');
require_once('../lib/OauthPhirehose.php');

function wd_remove_accents($str, $charset='utf-8')
{
    $str = htmlentities($str, ENT_NOQUOTES, $charset);
    
    $str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
    $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
    $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères
    
    return $str;
}

/**
 * Example of using Phirehose to display a live filtered stream using track words
 */
class FilterTrackConsumer extends OauthPhirehose
{
  /**
   * Enqueue each status
   *
   * @param string $status
   */
  public function enqueueStatus($status)
  {
    /*
     * In this simple example, we will just display to STDOUT rather than enqueue.
     * NOTE: You should NOT be processing tweets at this point in a real application, instead they should be being
     *       enqueued and processed asyncronously from the collection process.
     */

    global $all_kws;
    global $all_kws_flip;

    $id= '';
    $text = '';

  
    $data = json_decode($status, true);
    if (is_array($data) && isset($data['user']['screen_name'])) {
      $d = wd_remove_accents($data['text']);
      print $data['user']['screen_name'] . ': ' . urldecode($d). "\n";
      foreach ($all_kws as $key => $value) {
        if (stristr(urldecode($d),$key)) $id = $value;
        # code...
      }
      if (1) {
        echo "\n";
       
        if ($id == 1) {
            echo "match => ";
            $text = $all_kws_flip[$id];
            echo $text;
            vibrate(7,1);
        }

        if ($id == 2) {
            echo "match => ";
            $text = $all_kws_flip[$id];
            echo $text;
            vibrate(12,3);
        }

        if ($id == 3) {
            echo "match => ";
            $text = $all_kws_flip[$id];
            echo $text;
            vibrate(17,5);
        }
        echo "\n\n";
        echo "-------------------------------------------------------------------\n";
      }else {
        echo "no id\n\n";
      }
	
    }
  }
}

// The OAuth credentials you received when registering your app at Twitter
define("TWITTER_CONSUMER_KEY", "");
define("TWITTER_CONSUMER_SECRET", "");


// The OAuth data for the twitter account
define("OAUTH_TOKEN", "");
define("OAUTH_SECRET", "");

$all_kws = array();
$all_kws['#letagparfait'] = 1;
$all_kws['#lebonfap'] = 2;
$all_kws['#twittergasm'] = 3;


print_r($all_kws);
$all_kws_flip = array_flip($all_kws);
print_r($all_kws_flip);


echo "\n";

function vibrate($val,$delay = 1) {
  $x = file_get_contents("http://192.168.1.40:14245/Vibrate?v=" . $val );
  sleep($delay);
  $x = file_get_contents("http://192.168.1.40:14245/Vibrate?v=0");
}

//vibrate(10);

//exit();
// Start streaming
$sc = new FilterTrackConsumer(OAUTH_TOKEN, OAUTH_SECRET, Phirehose::METHOD_FILTER);
$sc->setTrack(array_keys($all_kws));
$sc->consume();
