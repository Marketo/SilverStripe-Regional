<?php

/**
 * @author Kirk Mayo <kirk.mayo@solnet.co.nz>
 *
 * A model for storing extra info for a region
 */

class GeoRegion extends DataObject
{
    private static $db = array(
        'Name' => 'Varchar',
        'RegionCode' => 'Varchar',
        'TimeZone' => 'Varchar'
    );

    //private static $has_one = array(
        //'Currency' => 'Currency'
    //);

    public function getCMSFields() {
        $fields = parent::getCMSFields();
        if ($this->ID && $this->RegionCode) {
            $readOnlyCode = ReadonlyField::create('RegionCode', 'Region code');
            $fields->replaceField('RegionCode', $readOnlyCode);
        }
        return $fields;
    }
}

class GeoRegionAdmin extends ModelAdmin {
    private static $managed_models = array(
        'GeoRegion'
    );

    private static $url_segment = 'geo-region-admin';

    private static $menu_title = 'Region Admin';

}
