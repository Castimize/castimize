<?php

namespace App\Virtual\Models;

use DateTime;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="User",
 *     description="User model",
 *     @OA\Xml(
 *         name="User"
 *     )
 * )
 */
class User
{
    /**
     * @OA\Property(
     *     title="ID",
     *     description="ID",
     *     format="int64",
     *     example=1
     * )
     *
     * @var integer
     */
    private $id;

    /**
     * @OA\Property(
     *     title="Avatar",
     *     description="Avatar",
     * )
     *
     * @var string
     */
    private $avatar;

    /**
     * @OA\Property(
     *     title="Name",
     *     description="Name",
     * )
     *
     * @var string
     */
    private $name;

    /**
     * @OA\Property(
     *     title="First name",
     *     description="First name",
     * )
     *
     * @var string
     */
    private $first_name;

    /**
     * @OA\Property(
     *     title="Last name",
     *     description="Last name",
     * )
     *
     * @var string
     */
    private $last_name;

    /**
     * @OA\Property(
     *     title="Email",
     *     description="Email",
     *     format="email",
     * )
     *
     * @var string
     */
    private $email;

    /**
     * @OA\Property(
     *     title="Email verified at",
     *     description="Email verified at",
     *     example="2020-01-27 17:50:45",
     *     format="datetime",
     *     type="string"
     * )
     *
     * @var DateTime
     */
    private $email_verified_at;

    /**
     * @OA\Property(
     *     title="Created at",
     *     description="Created at",
     *     example="2020-01-27 17:50:45",
     *     format="datetime",
     *     type="string"
     * )
     *
     * @var DateTime
     */
    private $created_at;

    /**
     * @OA\Property(
     *     title="Creator",
     *     description="Creator's name"
     * )
     *
     * @var string
     */
    private $creator;

    /**
     * @OA\Property(
     *     title="Updated at",
     *     description="Updated at",
     *     example="2020-01-27 17:50:45",
     *     format="datetime",
     *     type="string"
     * )
     *
     * @var DateTime
     */
    private $updated_at;

    /**
     * @OA\Property(
     *     title="Editor",
     *     description="Editor's name"
     * )
     *
     * @var string
     */
    private $editor;

    /**
     * @OA\Property(
     *     title="Deleted at",
     *     description="Deleted at",
     *     example="2020-01-27 17:50:45",
     *     format="datetime",
     *     type="string"
     * )
     *
     * @var DateTime
     */
    private $deleted_at;

    /**
     * @OA\Property(
     *     title="Destroyer",
     *     description="Destroyer's name"
     * )
     *
     * @var string
     */
    private $destroyer;
}
