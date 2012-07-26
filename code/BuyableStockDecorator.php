<?php
class BuyableStockDecorator extends DataObjectDecorator {
	
	function extraStatics(){
		return array(
			'db' => array(
				'Stock' => 'Int',
				'UnlimitedStock' => 'Boolean'
			)
		);
	}
	
	public static $min_alert_number = 10;
	
	function updateSummaryFields(Fieldset &$fields) {
        $fields['TitleForTable'] = 'Stock';
    }
    
	public function TitleForTable(){
		$title = "";
		if($this->owner instanceOf SiteTree) {
			if(!$this->owner->Variations()->exists()){
				if($this->owner->UnlimitedStock == 1){
					$title = "Unlimited";
				}else{
					$title = $this->owner->Stock;
				}
			}else{
				$variations = $this->owner->Variations();
				$stock = 0;
				foreach ($variations as $variation){
					if($variation->UnlimitedStock == 0){
						$stock += $variation->Stock;
					}
				}
				$title = $stock;
			}
		}else{
			if($this->owner->UnlimitedStock == 1){
				$title = "Unlimited";
			}else{
				$title = $this->owner->Stock;
			}
		}
		
		return $title;
	}
    
	function updateCMSFields(&$fields){
		if($this->owner instanceOf SiteTree) {
			$tabName = "Root.Content.Main";
			$fieldName = "FeaturedProduct";
			if(!$this->owner->Variations()->exists()){
				$fields->addFieldToTab($tabName, new NumericField('Stock','Stock'),$fieldName);
				$fields->addFieldToTab($tabName, new CheckboxField('UnlimitedStock','Unlimited Stock? (over-rides Stock field above)'),$fieldName);
			}else{
				$fields->addFieldToTab($tabName, new LiteralField('note','<p>Because you have one or more variations, the stock is a calculation of all stock levels</p><h4>Total Variations in Stock: '.$this->getVariationsStock().'</h4>'),$fieldName);
			}
		} else { // it's a variation and hopefully has fields
			$fields->insertAfter(new NumericField('Stock','Stock'),"Price");
			$fields->insertAfter(new CheckboxField('UnlimitedStock','Unlimited Stock? (over-rides Stock field above)'),"Stock");
		}
	}
	
	public function getMinAlertNumber(){
		return self::$min_alert_number;
	}
	
	public function getStockIsLow(){
		if($this->owner->UnlimitedStock == 0){
			$stock = 0;
			if(!$this->owner->Variations()->exists()){
				$stock = $this->owner->Stock;
			}else{
				$variations = $this->owner->Variations();
				foreach ($variations as $variation){
					if($variation->UnlimitedStock == 0){
						$stock += $variation->Stock;
					}
				}
			}
			if($stock <= self::$min_alert_number){
				return true;
			}
		}
	}
	
	public function getVariationsStock(){
		$stock = 0;
		if($this->owner instanceOf SiteTree) {
			if($this->owner->UnlimitedStock == 0){
				if(!$this->owner->Variations()->exists()){
					$stock = $this->owner->Stock;
				}else{
					$variations = $this->owner->Variations();
					foreach ($variations as $variation){
						if($variation->UnlimitedStock == 0){
							$stock += $variation->Stock;
						}
					}
				}
			}
		}
		return $stock;
	}

	
}
