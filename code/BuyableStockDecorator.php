<?php
class BuyableStockDecorator extends DataExtension {

	public static $db = array(
		'Stock' => 'Int',
		'UnlimitedStock' => 'Boolean'
	);

	//low stock strategies
	const SINGLEVARIATION = 0; // a single variation falls below the low stock threshhold
	const ALLVARIATIONS = 1; //the total stock level falls below the threshhold
	public static $low_stock_threshhold = 10;
	public static $low_stock_strategy = self::SINGLEVARIATION;
	
	function updateSummaryFields(&$fields) {
        $fields['TitleForTable'] = 'Stock';
    }

    function canPurchase($member = null) {
    	$allowpurchase = false;
    	if($this->owner instanceOf SiteTree) {
	   	}else{
	   		if($product = $this->owner->Product()){
    			$allowpurchase = ($this->owner->sellingPrice() > 0) && $product->AllowPurchase;
			}
			if($this->getVariationsStock() < 1){
				$allowpurchase = false;
			}
    		return $allowpurchase;
    	}
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
    
	public function updateCMSFields(FieldList $fields) {
		if($this->owner instanceOf SiteTree) {
			$tabName = "Root.Main";
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
		return self::$low_stock_threshhold;
	}
	
	public function getStockIsLow(){
		$variations = $this->owner->Variations();
		if($variations->exists()){
			if(self::$low_stock_strategy === self::SINGLEVARIATION){
				//any single variation falls below
				return $this->getStockedVariations()
					->filter('UnlimitedStock',0)
					->filter('Stock:LessThan', $this->getMinAlertNumber())
					->exists();
			}else{
				//total variations stock level falls below
				return $this->getVariationsStock() < $this->getMinAlertNumber();
			}
		}
		//no variations, use default stock value
		return !$this->UnlimitedStock && $this->Stock < $this->getMinAlertNumber();
	}
	
	public function getVariationsStock(){
		$stock = 0;
		if($this->owner instanceOf SiteTree && !$this->owner->UnlimitedStock) {
			$variations = $this->owner->Variations();
			if(!$variations->exists()){
				$stock = $this->owner->Stock;
			}else{
				return $this->getStockedVariations()
					->sum('Stock');
			}
		}
		return $stock;
	}

	public function getStockedVariations(){
		return $this->owner->Variations()
					->filter('ProductID',$this->owner->ID)
					->filter('UnlimitedStock',0);
	}

	
}
