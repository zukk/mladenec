<xml version="1.0" encoding="UTF-8">
    <Products>
        {foreach from=$goods item=i}
        <Product MerchantSKU="{$i->id1c}" ProductTypeID="{$i->ozon_type_id}">
            <SKU>
                <Name>{$i->group_name} {$i->name}</Name>
                <ManufacturerIdentifier>{$i->barcode}</ManufacturerIdentifier>
                <GrossWeight>{if strpos($i->prop->weight, '.') !== FALSE}{$i->prop->weight*1000}{else}{$i->prop->weight}{/if}</GrossWeight>
            </SKU>
            <Price>
                <SellingPrice>{$i->price}</SellingPrice>
                <Discount>0</Discount>
            </Price>
            <Availability>
                <SellingState>{if $i->qty !=0 and $i->active eq 1 and $i->show}ForSale{else}NotForSale{/if}</SellingState>
                <SupplyPeriod>In3Days</SupplyPeriod>
                <Qty>{if $i->qty eq -1}10{else}{$i->qty}{/if}</Qty>
            </Availability>
            <Description>
                <Name>{$i->group_name} {$i->name}</Name>
                <Picture>{$i->prop->image500->get_url()}</Picture>
                <Capability>
                    <Type>

                    </Type>
                    <Name>{$i->group_name} {$i->name}</Name>
                    <Brand>
                        <Name>{$i->brand->name}</Name>
                    </Brand>
                    <Material>

                    </Material>
                    <ExternalID>{$i->barcode}</ExternalID>
                </Capability>
                <Color>
                    <Name>{$i->name}</Name>
                </Color>
                <Brand>
                    <Name>{$i->brand->name}</Name>
                </Brand>

            </Description>
        </Product>
        {/foreach}
    </Products>
</xml>