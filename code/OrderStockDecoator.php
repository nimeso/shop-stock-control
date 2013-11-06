<?php
class OrderStockDecoator extends DataExtension {
	
	public static $db = array(
		'StockHasBeenCalculated' => 'Boolean'
	);
	
	function onBeforeWrite(){ 
      	if($this->owner->Status == 'Paid' && $this->owner->StockHasBeenCalculated == 0){
      		$this->owner->StockHasBeenCalculated = 1;
      		if($orderItems = $this->owner->Items()){
      			foreach ($orderItems as $orderItem){
	      			if($buyable = $orderItem->Buyable()){
	      				if($buyable->UnlimitedStock == 0){
		      				$oldNum = $buyable->Stock;
		      				$newNum = $oldNum - $orderItem->Quantity;
		      				$buyable->Stock = $newNum;
		      				$buyable->write();
	      				}
	      			}
      			}
      		}
      	}      	      	if(($this->owner->Status == 'AdminCancelled' && $this->owner->StockHasBeenCalculated == 1) || ($this->owner->Status == 'MemberCancelled' && $this->owner->StockHasBeenCalculated == 1)){      		if($orderItems = $this->owner->Items()){
      			foreach ($orderItems as $orderItem){
      				if($buyable = $orderItem->Buyable()){
      						$oldNum = $buyable->Stock;
      						$newNum = $oldNum + $orderItem->Quantity;
      						$buyable->Stock = $newNum;
      						$buyable->write();
      				}
      			}
      		}      	}
      	parent::onBeforeWrite();
   	} 
}