<?php

namespace App\Api\V1\Controllers;

use App\Exceptions\ContributorRegistrationException;
use App\Http\Controllers\Controller;
use App\Models\Contributor;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use JetBrains\PhpStorm\ArrayShape;
use Sumra\JsonApi\JsonApiResponse;

/**
 * Class ContributorController
 *
 * @package App\Api\V1\Controllers
 */
class ContributorController extends Controller
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Contributor::class;

    /**
     * ContributorController constructor.
     *
     * @param Contributor $model
     */
    public function __construct(Contributor $model)
    {
        $this->model = $model;
    }

    /**
     * Contributor registration
     * Step 2. Saving contributor person detail
     *
     * @OA\Post(
     *     path="/contributors/person",
     *     summary="Saving contributor person detail",
     *     description="Saving contributor person detail",
     *     tags={"Contributors"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *     x={
     *         "auth-type": "Application & Application User",
     *         "throttling-tier": "Unlimited",
     *         "wso2-application-security": {
     *             "security-types": {"oauth2"},
     *             "optional": "false"
     *         }
     *     },
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ContributorPerson")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successfully save"
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Contributor created"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="not found"
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Validation failed"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     )
     * )
     *
     * @param Request $request
     * @return array
     * @throws ContributorRegistrationException
     */
    public function store(Request $request): array
    {
        // Validate input
        try {
            $this->validate($request, $this->model::personValidationRules());
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'type' => 'warning',
                'title' => 'Contributor details data',
                'message' => "Validation error",
                'data' => $e->getMessage()
            ], 400);
        }

        // Try to save received data
        try {
            $contributor_id = $request->get('id', null);

            // Find Exist contributor
            $contributor = $this->model::find($contributor_id);

            if (!$contributor) {
                // Create new
                $contributor = $this->model::create([
                    'user_id' => Auth::user()->getAuthIdentifier(),
                    'status' => Contributor::STATUS_STEP_1
                ]);

                unset($request->id);
            }

            $contributor->fill($request->all());
            $contributor->fill($request->get('address'));

            $contributor->status = Contributor::STATUS_STEP_2;
            $contributor->save();

            // Return response to client
            return [
                'type' => 'success',
                'title' => 'New contributor registration',
                'message' => "Contributor person detail successfully saved",
                'data' => $contributor->toArray()
            ];
        } catch (Exception $e) {
            throw new ContributorRegistrationException($e);
        }
    }

    /**
     * Contributor registration
     * Step 3. Saving contributor Identify data
     *
     * @OA\Put(
     *     path="/contributors/identify/{id}",
     *     summary="Saving contributor identify data",
     *     description="Saving contributor identify data",
     *     tags={"Contributors"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *     x={
     *         "auth-type": "Application & Application User",
     *         "throttling-tier": "Unlimited",
     *         "wso2-application-security": {
     *             "security-types": {"oauth2"},
     *             "optional": "false"
     *         }
     *     },
     * 
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Contributor Id",
     *         example="0aa06e6b-35de-3235-b925-b0c43f8f7c75",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ContributorIdentify")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successfully save"
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Contributor created"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="not found"
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Validation failed"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     )
     * )
     * 
     * @param Request $request
     * @return array
     * @throws ContributorRegistrationException
     */
    public function update($id, Request $request)
    {
        // Validate input
        try {
            $this->validate($request, $this->model::identifyValidationRules());
        } catch (ValidationException $e) {
            return response()->jsonApi([
                'type' => 'warning',
                'title' => 'Contributor details data',
                'message' => "Validation error",
                'data' => $e->getMessage()
            ], 400);
        }

        // Try to save received document data
        try {
            // Find Exist contributor
            $contributor = $this->getObject($id);
            if (is_a($contributor, 'Sumra\JsonApi\JsonApiResponse')) {
                return $contributor;
            }

            $contributor->fill($request->all());

            $document = [];
            foreach ($request->get('document') as $key => $value) {
                $document['document_' . $key] = $value;
            }
            $contributor->fill($document);
            $contributor->status = Contributor::STATUS_STEP_3;
            $contributor->save();

            // Return response to client
            return [
                'type' => 'success',
                'title' => 'New contributor registration',
                'message' => "Contributor Identify data successfully saved",
                'data' => $contributor->toArray()
            ];
        } catch (Exception $e) {
            throw new ContributorRegistrationException($e);
        }
    }

    /**
     * Getting data already provided by the contributor
     *
     * @OA\Get(
     *     path="/contributors/{id}",
     *     summary="Getting data already provided by the contributor",
     *     description="Getting data already provided by the contributor",
     *     tags={"Contributors"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *     x={
     *         "auth-type": "Application & Application User",
     *         "throttling-tier": "Unlimited",
     *         "wso2-application-security": {
     *             "security-types": {"oauth2"},
     *             "optional": "false"
     *         }
     *     },
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Contributors ID",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Detail data of contributor"
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="Contributor not found"
     *     )
     * )
     */
    public function show($id)
    {
        // Get object
        $contributor = $this->getObject($id);

        if ($contributor instanceof JsonApiResponse) {
            return $contributor;
        }

        // Load linked relations data
        $contributor->load([
            'phones',
            'emails',
            'contributors',
            'addresses'        
        ]);

        // Read big size of avatar from storage
//        $contributor->setAttribute('avatar', $this->getImagesFromRemote($id, 'big'));

        return response()->jsonApi([
            'type' => 'success',
            'title' => 'Contributor details data',
            'message' => "Contributor detail data has been received",
            'data' => $contributor->toArray()
        ], 200);
    }

    /**
     * Get contributor object
     *
     * @param $id
     * @return mixed
     */
    private function getObject($id)
    {
        try {
            return $this->model::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Get contributor",
                'message' => "Contributor with id #{$id} not found: {$e->getMessage()}",
                'data' => null
            ], 404);
        }
    }
}
