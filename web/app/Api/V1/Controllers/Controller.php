<?php

namespace App\Api\V1\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     title=SWAGGER_TITLE,
 *     description=SWAGGER_DESCRIPTION,
 *     version=SWAGGER_VERSION,
 *
 *     @OA\Contact(
 *         email=SWAGGER_SUPPORT_EMAILS,
 *         name="Support Team"
 *     )
 * )
 */

/**
 * @OA\Server(
 *      url=SWAGGER_CONST_HOST,
 *      description=SWAGGER_DESCRIPTION
 * )
 */

/**
 * @OA\SecurityScheme(
 *     type="oauth2",
 *     description="Auth Scheme",
 *     name="oAuth2 Access",
 *     securityScheme="default",
 *
 *     @OA\Flow(
 *         flow="implicit",
 *         authorizationUrl="https://sumraid.com/oauth2",
 *         scopes={
 *             "ManagerRead"="Manager can read",
 *             "User":"User access",
 *             "ManagerWrite":"Manager can write"
 *         }
 *     )
 * )
 */

/**
 * @OA\SecurityScheme(
 *      in="header",
 *      type="http",
 *      scheme="bearer",
 *      name="bearerAuth",
 *      bearerFormat="JWT",
 *      description="Auth Token",
 *      securityScheme="bearerAuth",
 * ),
 */

/**
 * @OA\SecurityScheme(
 *      in="header",
 *      type="apiKey",
 *      name="User-Id",
 *      description="Auth User ID",
 *      securityScheme="apiKey",
 * ),
 */

/**
 * ApiResponse Schema
 *
 * @OA\Schema(
 *     schema="ApiResponse",
 *
 *     @OA\Property(
 *         property="type",
 *         type="string",
 *         description="Type of response (success, danger, warning)"
 *     ),
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         description="Title of response"
 *     ),
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         description="Message of response"
 *     )
 * )
 */

/**
 * Success Response
 *
 * @package App\Api\V1\Controllers
 *
 * @OA\Schema(
 *      schema="OkResponse",
 *
 *      @OA\Property(
 *          property="type",
 *          type="string",
 *          example="success"
 *      ),
 *      @OA\Property(
 *          property="title",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="message",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="data",
 *          type="object"
 *      ),
 * )
 *
 */


/**
 * Warning Response
 *
 * @package App\Api\V1\Controllers
 *
 * @OA\Schema(
 *      schema="InfoResponse",
 *
 *      @OA\Property(
 *          property="type",
 *          type="string",
 *          example="info"
 *      ),
 *      @OA\Property(
 *          property="title",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="message",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="data",
 *          type="object"
 *      ),
 * )
 *
 */

/**
 * Warning Response
 *
 * @package App\Api\V1\Controllers
 *
 * @OA\Schema(
 *      schema="WarningResponse",
 *
 *      @OA\Property(
 *          property="type",
 *          type="string",
 *          example="warning"
 *      ),
 *      @OA\Property(
 *          property="title",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="message",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="data",
 *          type="object"
 *      ),
 * )
 *
 */


/**
 * Danger Response
 *
 * @package App\Api\V1\Controllers
 *
 * @OA\Schema(
 *      schema="DangerResponse",
 *
 *      @OA\Property(
 *          property="type",
 *          type="string",
 *          example="danger"
 *      ),
 *      @OA\Property(
 *          property="title",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="message",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="data",
 *          type="object"
 *      ),
 * )
 *
 */

/**
 * Api Base Class Controller
 *
 * @package App\Api\V1\Controllers
 */
class Controller extends BaseController{}
