<?php
class OrderStockDecoator extends DataObjectDecorator{
	
	function extraStatics(){
		return array(
			'db' => array(
				'StockHasBeenCalculated' => 'Boolean'
			)
		);
	}
	
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
      		
      	}
      	parent::onBeforeWrite();
   	} 
}