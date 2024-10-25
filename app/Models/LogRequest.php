<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;

class LogRequest extends Model
{
    use HasFactory, SoftDeletes, RevisionableTrait, Userstamps;

    protected $revisionForceDeleteEnabled = true;
    protected $revisionCreationsEnabled = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected  $fillable = [
        'type',
        'path_info',
        'request_uri',
        'method',
        'remote_address',
        'user_agent',
        'server',
        'headers',
        'request',
        'response',
        'http_code',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
            'server' => AsArrayObject::class,
            'headers' => AsArrayObject::class,
            'request' => AsArrayObject::class,
            'response' => AsArrayObject::class,
        ];
    }
}
