<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Paysera\Transaction;

class TransactionTest extends TestCase {
    public function testCSVImport(){  //read all datas now

        if (($handle = fopen('input.csv', "r")) !== FALSE) {
           $row = 0;
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                $num = count($data);
                $row = $row + 1;
                
             }
            fclose($handle);
            $this->assertEquals(9,$row); //we have 9 datas in test now
        }
        
    }

    public function testPriceInEuro(){

        $conversionRates = array(
            "EUR" => 1, 
            "USD" => 1.1497,
            "JPY" => 129.53
        );
        
        $trans = new Transaction();

        for($i = 0; $i < 100; $i++) {
            $random = (float)rand();
            $curr = array_rand($conversionRates);
            $result = $trans->getPriceInEuro($random, $curr);

            $this->assertEquals(round($random * (1 /$conversionRates[$curr]),4), $result);

        }

    }

    public function testconvertLocalCurrency(){

        $conversionRates = array(
            "EUR" => 1, 
            "USD" => 1.1497,
            "JPY" => 129.53
        );
        
        $trans = new Transaction();

        for($i = 0; $i < 100; $i++) {
            $random = (float)rand();
            $curr = array_rand($conversionRates);
            $result = $trans->convertLocalCurrency($random, $curr);

            $this->assertEquals(round($random * $conversionRates[$curr],4), $result);

        }
    }

    public function testcalculateCommFee(){

        $trans = new Transaction('2016-01-05','1','natural','cash_in','200.00','EUR');
            
        $transArray[] = $trans; 
        $feet = $trans->calculateCommFee($transArray);
        
        $this->assertEquals(0.06, $feet);
    }
}
?>