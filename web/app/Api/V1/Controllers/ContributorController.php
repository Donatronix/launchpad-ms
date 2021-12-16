<?php

namespace App\Api\V1\Controllers;

use App\Exceptions\ContributorRegistrationException;
use App\Http\Controllers\Controller;
use App\Models\Contributor;
use Exception;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
     * ContributorsController constructor.
     *
     * @param Contributor $model
     */
    public function __construct(Contributor $model)
    {
        $this->model = $model;

    }

    /**
     * Save a new contributor data
     *
     * @OA\Post(
     *     path="/contributors/{step}",
     *     summary="Save a new contributor data",
     *     description="Save a new contributor data",
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
     *         name="step",
     *         description="Registration step (1 - login, 2 - person detail, 3 - document detail, 4 - payment)",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             enum={"person", "document"},
     *             default="person"
     *         )
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Contributor")
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
     */
    public function store(string $step, Request $request)
    {
        // Check $step value
        if(!in_array($step, ['login', 'person', 'document', 'payment'])){
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'New contributor registration',
                'message' => "Registration step '{$step}' does not exist",
                'data' => null
            ], 400);
        }

        // Validate input
        try {
            $this->validate($request, $this->model::{"{$step}ValidationRules"}($step));
        } catch (ValidationException $e){
            dd($e);
        }

        // Try to save received data
        try {
            // Run step action
            $result = $this->{"step$step"}($request);

            // Return response to client
            return response()->jsonApi($result, 200);

        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'New contributor registration',
                'message' => $e->getMessage(),
                'data' => null
            ], 400);
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
            'works',
            'addresses',
            'sites',
            'chats',
            'relations'
        ]);

        // Read big size of avatar from storage
        $contributor->setAttribute('avatar', $this->getImagesFromRemote($id, 'big'));

        return response()->jsonApi([
            'type' => 'success',
            'title' => 'Contributor details data',
            'message' => "Contributor detail data has been received",
            'data' => $contributor->toArray()
        ], 200);
    }

    /**
     * Update contributor data
     *
     * @OA\Put(
     *     path="/contributors/{id}",
     *     summary="Update contributor data",
     *     description="Update contributor data",
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
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Contributor")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successfully save"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        // Validate input
        $this->validate($request, $this->model::validationRules());

        // Read contributor model
        $contributor = $this->getObject($id);
        if (is_a($contributor, 'Sumra\JsonApi\JsonApiResponse')) {
            return $contributor;
        }

        // Try update contributor data
        try {

            // Update data
            $contributor->fill($request->all());
            $contributor->save();

            // Return response to client
            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Changing contributor',
                'message' => "Contributor {$contributor->phone} successfully updated",
                'data' => $contributor->toArray()
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Change a contributor',
                'message' => $e->getMessage(),
                'data' => null
            ], 400);
        }
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

    /**
     * Contributor registration
     * Step 1. Create contributor object
     *
     * @param Request $request
     * @return array
     * @throws ContributorRegistrationException
     */
    #[ArrayShape(['type' => "string", 'title' => "string", 'message' => "string", 'data' => "mixed"])]
    private function stepLogin(Request $request): array
    {
        // Try to save received data
        try {
            // Create new
            $contributor = $this->model::create([
                'user_id' => Auth::user()->getAuthIdentifier(),
                'status' => Contributor::STATUS_STEP_1
            ]);

            // Return response to client
            return [
                'type' => 'success',
                'title' => 'New contributor registration',
                'message' => "Contributor object successfully created",
                'data' => $contributor->toArray()
            ];
        } catch (Exception $e) {
            throw new ContributorRegistrationException($e);
        }
    }

    /**
     * Contributor registration
     * Step 2. Saving contributor person detail
     *
     * @param Request $request
     * @return array
     * @throws ContributorRegistrationException
     */
    #[ArrayShape(['type' => "string", 'title' => "string", 'message' => "string", 'data' => "mixed"])]
    private function stepPerson(Request $request): array
    {
        // Try to save received data
        try {
            $contributor_id = $request->get('id', null);

            // Find Exist contributor
            $contributor = $this->model::find($contributor_id);

            if(!$contributor){
                // Create new
                $contributor = $this->model::create([
                    'user_id' => Auth::user()->getAuthIdentifier()
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
     * Step 3. Saving contributor document data
     *
     * @param Request $request
     * @return array
     * @throws ContributorRegistrationException
     */
    private function stepDocument(Request $request){
       // Try to save received document data
        try {
            $contributor_id = $request->get('id', null);

            // Find Exist contributor
            $contributor = $this->model::find($contributor_id);

            $document = [];
            foreach($request->get('document') as $key => $value){
                $document['document_' . $key] = $value;
            }
            $contributor->fill($document);
            $contributor->status = Contributor::STATUS_STEP_3;
            $contributor->save();

            // Return response to client
            return [
                'type' => 'success',
                'title' => 'New contributor registration',
                'message' => "Contributor document successfully saved",
                'data' => $contributor->toArray()
            ];
        } catch (Exception $e) {
            throw new ContributorRegistrationException($e);
        }
    }

    /**
     * Contributor registration
     * Step 4. Saving contributor payment data and pay process
     *
     * @return void
     */
    private function stepPayment(){

    }
}
