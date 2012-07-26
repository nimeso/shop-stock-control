<?php
DataObject::add_extension("Order","OrderStockDecoator");
Object::add_extension("Product", "BuyableStockDecorator");
Object::add_extension("ProductVariation", "BuyableStockDecorator");