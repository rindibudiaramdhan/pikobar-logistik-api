<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LogisticRealizationItems extends Model
{
    use SoftDeletes;
    
    const STATUS = [
        'delivered',
        'not_delivered',
        'approved',
        'not_approved',
        'not_available',
        'replaced'
    ];

    protected $table = 'logistic_realization_items';

    protected $fillable = [
        'id',
        'agency_id',
        'applicant_id',
        'need_id',
        'product_id',
        'product_name',
        'realization_unit',
        'material_group',
        'realization_quantity',
        'unit_id',
        'status',
        'realization_date',
        'created_by',
        'updated_by'
    ];

    public function agency()
    {
        return $this->belongsToMany('App\Agency', 'id', 'agency_id');
    }

    public function product()
    {
        return $this->hasOne('App\Product', 'id', 'product_id');
    }

    public function unit()
    {
        return $this->hasOne('App\MasterUnit', 'id', 'unit_id');
    }
}
