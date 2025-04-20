<?php

namespace Modules\{Module}\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;  // para uso de Uuids
use Illuminate\Database\Eloquent\SoftDeletes;

//use Illuminate\Database\Eloquent\Relations\HasOne;
//use Illuminate\Database\Eloquent\Relations\BelongsTo;
//use Illuminate\Database\Eloquent\Relations\HasMany;
//use Illuminate\Database\Eloquent\Relations\HasOneThrough;
//use Illuminate\Database\Eloquent\Relations\HasManyThrough;

use Modules\{Module}\Database\Factories\{Model}Factory;

class {Model} extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table      = '{table}';   // table name
    protected $primaryKey = 'id';        // key name
    protected $keyType    = 'string';    // uuid key
    public $incrementing  = false;       // uuid key

    // fields that can be filled
    protected $fillable = [
        'name'
    ];

    // relations that can be loaded
    protected $with = [ ];

    // default values for attributes
    protected $attributes = [
        //'options' => '[]',
        //'delayed' => false,
    ];
    
    protected static function newFactory(): {Model}Factory
    {
        return {Model}Factory::new();
    }

    /**
     * Other sample stub
     */

    /*
    
    public function phone(): HasOne
    {
        return $this->hasOne(Phone::class, 'foreign_key', 'local_key');

        // Other usefull codes...
        //return $this->hasOne(Order::class)->latestOfMany();
        //return $this->hasOne(Order::class)->oldestOfMany();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'foreign_key', 'owner_key');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'foreign_key', 'local_key');
    }

    public function carOwner(): HasOneThrough
    {
    return $this->hasOneThrough(
        Owner::class,
        Car::class,
        'mechanic_id', // Foreign key on the cars table...
        'car_id', // Foreign key on the owners table...
        'id', // Local key on the mechanics table...
        'id' // Local key on the cars table...
    );

    
    public function deployments(): HasManyThrough
    {
        return $this->hasManyThrough(
            Deployment::class,
            Environment::class,
            'application_id', // Foreign key on the environments table...
            'environment_id', // Foreign key on the deployments table...
            'id', // Local key on the applications table...
            'id' // Local key on the environments table...
        );
    }
    */

}
