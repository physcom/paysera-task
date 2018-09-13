<?php


require __DIR__ . '/vendor/autoload.php';

use Paysera\Transaction;

/* Read CSV File*/


$transArray = [];
$row = 1;

$rowLength = 1000;
$delimiter = ",";

if(isset($argv[1])){
    if (($handle = fopen($argv[1], "r")) !== FALSE) {
        while (($data = fgetcsv($handle, $rowLength, $delimiter)) !== FALSE) {
            $num = count($data);

            $row++;
            
            $trans = new Transaction($data[0],$data[1],$data[2],$data[3],$data[4],$data[5]);
            
            $transArray[] = $trans; 
            $feet = $trans->calculateCommFee($transArray);
            //$transArray[] = $trans; //$transArray['user_id'] = []
            
            //echo  $data[1].",".$data[2].",".$data[3].",".$data[4].",".$data[5]." => ".$feet."\n";
            echo  $feet."\n";
            
           
         }
        fclose($handle);
    }
}
else {
    echo "No csv file supplied.\n";
}

?>
