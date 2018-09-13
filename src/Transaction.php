<?php

namespace Paysera;

use Danhunsaker\BC; //Used to multiplication of decimals with precision


class Transaction{
     /* Class variables */
      public $conversionRates = array(
        "EUR" => 1, 
        "USD" => 1.1497,
        "JPY" => 129.53
      );

      private $minCommFee = 0.50;
      private $maxCommFee = 5.00;
      private $freeCommLimit = 1000.00;
      private $freeCommCount = 3;
      private $cashInCommPercent = 0.03;
      private $cashOutCommPercent = 0.3;

      private $date;
      private $user_id;
      private $u_type;
      private $o_type;
      private $o_amount;
      private $o_currency;
      
      public function __construct( $par1=0, $par2=0, $par3=0, $par4=0, $par5=0, $par6=0 ) {
	   $this->date = $par1;
	   $this->user_id = $par2;
	   $this->u_type = $par3;
	   $this->o_type = $par4;
	   $this->o_amount = $par5;
	   $this->o_currency = $par6;
      }
      
      /* Class methods */

      public function getPriceInEuro($amount, $currency){

        foreach($this->conversionRates as $curr => $rate){
            if($curr == $currency){
                return round($amount * (1/$rate),4);
            }
        }
        
          
      }
      public function convertLocalCurrency($amount, $currency){

        foreach($this->conversionRates as $curr => $rate){
            if($curr == $currency){
                return round($amount * $rate,4);
            }
        }

      }
      public function roundUp($value, $precision) {
        $mult = pow(10, $precision);
        return number_format(ceil($value * $mult)/$mult, $precision);
      }

      public function calculateCommFee($dataArray){
        
         //cash_in 
        if($this->o_type == "cash_in"){
            
            $result = $this->getCashInFee($this->o_amount,$this->o_currency);
            return $this->roundUp($result, 2);
            
        }
        //cash_out and legal
        elseif($this->o_type == "cash_out" && $this->u_type == "legal"){
              
            $result = $this->getCashOutFee($this->o_amount,$this->o_currency);
            return $this->roundUp($result, 2);
          
        }
        //cash_out and natural
        else{
            
        
            $check = $this->checkEligibleDiscount( $dataArray,$this ); //check for count checkout in week and amount total
            $euroAmount = $this->getPriceInEuro($this->o_amount, $this->o_currency); 

            if($check['count'] > $this->freeCommCount){
                $fee = ( $this->cashOutCommPercent/100 ) * $euroAmount;
                $result = $this->convertLocalCurrency($fee, $this->o_currency);
                return $this->roundUp($result, 2);
            }
            
            $diff =  $check['amount'] - $this->freeCommLimit;
            //echo $check['amount']." count ".$check['count']." diff ".$diff." and => ".$euroAmount."\n";
            if ($diff <= 0) { //$fee <= 1000.00
                $result = 0;
                return $this->roundUp($result, 2);
            } 
            elseif ($diff > $euroAmount) { //$diff > this->amount,exceeded 1000
                $fee = ( $this->cashOutCommPercent/100 ) * $euroAmount;
                $result = $this->convertLocalCurrency($fee, $this->o_currency);
                return $this->roundUp($result, 2);
            } 
            else {
                $fee = ( $this->cashOutCommPercent/100 ) * $diff;
                $result = $this->convertLocalCurrency($fee, $this->o_currency);
                return $this->roundUp($result, 2);
            }
                    
                    
        }
        
         
      }

      public function getCashInFee($amount, $currency){

        $price_euro = $this->getPriceInEuro($amount, $currency); 
        $fee = ( $this->cashInCommPercent/100 )* $price_euro ;
      
        if( $fee > $this->maxCommFee ){ //$fee > 5.00
            return $this->convertLocalCurrency($this->maxCommFee, $currency);
        }
        return $this->convertLocalCurrency($fee,$currency);

      }
      public function getCashOutFee($amount, $currency){

        $price_euro = $this->getPriceInEuro($amount,$currency); 
        $fee = ( $this->cashOutCommPercent/100 ) * $price_euro;
       
        if( $fee < $this->minCommFee ){ //$fee < 0.5
            return $this->convertLocalCurrency($this->minCommFee, $currency);
        }
        return $this->convertLocalCurrency($fee, $currency);

      }

      public function checkEligibleDiscount($dataArray, $trans){
        $count = 0;
        $amount = 0;

        foreach ($dataArray as $data) {
            //echo $data->o_type." and user id ".$data->user_id." trans id ".$trans->user_id."\n";
            if( $data->user_id == $trans->user_id && $data->o_type == "cash_out" ){
                //echo " IN \n";
                $firstDate  = strtotime($data->date);
                $secondDate = strtotime($trans->date);
                $result = date('oW', $firstDate) === date('oW', $secondDate) && date('Y', $firstDate) === date('Y', $secondDate);
                if($result){ //same week number and same year
                    $count  = $count + 1;
                    $price_euro = $this->getPriceInEuro($data->o_amount,$data->o_currency);
                    $amount = $amount + $price_euro;
                }
                
            }
        }

    
        $result = array( 
            'count' => $count, 
            'amount' => $amount 
        );

        return $result;
        
      }


}




?>
