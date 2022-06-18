<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Sumra\SDK\Traits\UuidTrait;

/**
 * Contributor Person Scheme
 *
 * @package App\Models
 *
 * @OA\Schema(
 *     schema="Faq",
 *
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         description="Faq Title"
 *     ),
 *     @OA\Property(
 *         property="body",
 *         type="string",
 *         description="Faq content",
 *     ),
 *     @OA\Property(
 *         property="type",
 *         type="string",
 *         description="Faq Category",
 *     ),
 *     @OA\Property(
 *         property="icon",
 *         type="string",
 *         description="Faq Icon",
 *     )
 * )
 */

class Faq extends Model
{
    use HasFactory;
    use UuidTrait;

    protected $fillable = ['title', 'body', 'type', 'icon'];
}
