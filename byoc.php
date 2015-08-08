<?php
    $url = 'https://registration.quakecon.org/?action=byoc_data&response_type=json';
    $content = file_get_contents($url);
    $json = json_decode($content, true);

    $clans = $json['data']['tags'];
    $seats = [];

    foreach($json['data']['seats'] as $key => $val) {
        $seathash = $key;
        $clanhandle = explode(':',$val);
        $clanhash   = $clanhandle[0];
        $handle = $clanhandle[1];
        $clan = null;
        $seat = decode_seat($seathash);

        if ($clanhash != null) {
            $clan = $clans[$clanhash];
        }

        if ($handle == null) {
            $handle = "Reserved";
        }        

        $seats[$seat] = array('clan' => $clan, 'handle' => $handle);
    }

    ksort($seats);

    print "\n";
    foreach($seats as $key => $val) {
        print $key;

        if($val['clan'] != null) {
            print " " . $val['clan'];
        }

        print " " . $val['handle'] . "\n";
    }

    function decode_seat($num, $b=62) {
      $seat = '';
      $base='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
      $limit = strlen($num);
      $res=strpos($base,$num[0]);
      for($i=1;$i<$limit;$i++) {
        $res = $b * $res + strpos($base,$num[$i]);
      }

      if ($res < 740) {

        //ROW A
        $pos = $res + 1;
        $row = ceil($pos / 10);
        $col = ($pos % 10);
        if ($col == 0) {
            $col = 10;
        }
        $seat = "A" . $row . "-" . sprintf('%02d',$col);

      } elseif ($res < 2368) {

        //ROW B
        $pos = $res - (740 - 1);
        $row = ceil($pos / 22);
        $col = ($pos % 22);
        if ($col == 0) {
            $col = 22;
        }
        $seat = "B" . $row . "-" . sprintf('%02d',$col);

     } elseif ($res < 3136) {

        //ROW C
        $pos = $res - (2368 - 1);
        $row = ceil($pos / 12);
        $col = ($pos % 12);
        if ($col == 0) {
            $col = 12;
        }
        $seat = "C" . $row . "-" . sprintf('%02d',$col);

      } elseif ($res < 3256) {

        //UAC
        $pos = ($res - 3136);
        $row = floor($pos / 10) + 1;
        $col = floor($pos % 10) + 1;
        $dig = 13 - $row + (10 - $col);
        if ($col == 1) {
            $dig = $dig + (2 * ($row - 1));
        }        
        $seat = "UAC-" . sprintf('%02d',$dig);

      } else {
        $seat = "Unknown seat.";
      }

      return $seat;
    }
?>