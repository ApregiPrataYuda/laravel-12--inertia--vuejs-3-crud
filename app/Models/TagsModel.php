<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TagsModel extends Model
{
    use HasFactory,
         SoftDeletes;
    protected $table = 'tags';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;
    protected $fillable = [
        'name',
        'slug'
    ];

    public static function isTagsExists($name)
    {
        return self::where('name', $name)->exists();
    }

    public static function isTagsExistsEdit($name, $excludeId = null)
    {
        return self::where('name', $name)
            ->where('id', '!=', $excludeId)
            ->exists();
    }
}
