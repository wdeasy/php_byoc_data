<?php
    define("A", serialize (array ("aisle" => "A", "rows" => 74, "cols" => 10, "start" => -1, "end" => 740)));
    define("B", serialize (array ("aisle" => "B", "rows" => 74, "cols" => 22, "start" => 739, "end" => 2638)));
    define("C", serialize (array ("aisle" => "C", "rows" => 64, "cols" => 12, "start" => 2367, "end" => 3136)));
    define("UAC", serialize (array ("aisle" => "UAC", "rows" => 12, "cols" => 10, "start" => 3136, "end" => 3256)));

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
        $number = hash_convert($seathash);
        $seat = get_seat($number);

        if ($clanhash != null) {
            $clan = $clans[$clanhash];
        }

        if ($handle == null) {
            $handle = "Reserved";
        }        

        $seats[$seat['key']] = array('seat' => $seat['seat'], 'clan' => $clan, 'handle' => $handle);
    }

    ksort($seats);

    print "\n";
    foreach($seats as $key => $val) {
        print $val['seat'];

        if($val['clan'] != null) {
            print " " . $val['clan'];
        }

        print " " . $val['handle'] . "\n";
    }

    function hash_convert($num, $b=62) {
      $seat = '';
      $base='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
      $limit = strlen($num);
      $res=strpos($base,$num[0]);
      for($i=1;$i<$limit;$i++) {
        $res = $b * $res + strpos($base,$num[$i]);
      }
      return $res;        
    }

    function get_seat($num) {
      $A    = unserialize(A);
      $B    = unserialize(B);
      $C    = unserialize(C);
      $UAC  = unserialize(UAC);
      
      $seat = '';
      if ($num < $A['end']) {
        $seat = build_seat($num, $A);
      } elseif ($num < $B['end']) {
        $seat = build_seat($num, $B);
      } elseif ($num < $C['end']) {
        $seat = build_seat($num, $C);
      } elseif ($num < $UAC['end']) {
        $seat = build_seat($num, $UAC);
      } else {
        $seat['seat'] = "Unknown seat.";
        $seat['key'] = $num;
      }
      return $seat;
    }

    function build_seat($num, $array) {
        $seat = [];
        if ($array['aisle'] == "UAC") {
            $pos = ($num - $array['start']);
            $row = floor($pos / $array['cols']) + 1;
            $col = floor($pos % $array['cols']) + 1;
            $dig = 13 - $row + ($array['cols'] - $col);
            if ($col == 1) {
                $dig = $dig + (2 * ($row - 1));
            }        
            $seat['seat'] = "UAC-" . sprintf('%02d',$dig);
            $seat['key'] = "UAC-" . sprintf('%02d',$dig);
            $seat['aisle'] = $array['aisle'];
            $seat['row'] = $row;
            $seat['col'] = $col;
        } else {
            $pos = $num - $array['start'];
            $row = ceil($pos / $array['cols']);
            $col = ($pos % $array['cols']);
            if ($col == 0) {
                $col = $array['cols'];
            }
            $seat['seat'] = $array['aisle'] . $row . "-" . sprintf('%02d',$col); 
            $seat['key']  = $array['aisle'] . sprintf('%02d',$row) . "-" . sprintf('%02d',$col);
            $seat['aisle'] = $array['aisle'];
            $seat['row'] = $row;
            $seat['col'] = $col;           
        }
        return $seat;        
    }
?>