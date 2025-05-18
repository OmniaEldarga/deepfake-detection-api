<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class File extends Model
{
    use HasFactory , SoftDeletes;

    protected $table = 'table_files';
    protected $guarded = ['id'];

    protected $casts = [
        'check_date' => 'datetime',
        'report_date' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
