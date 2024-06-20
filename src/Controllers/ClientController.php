<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClientController extends Controller
{
    /*********************************************************
     * 
     *  Below Store Function is an example function 
     *  to create a new database each time a new
     *  client get created
     * 
    ***********************************************************/


    /**
     * postAddClient function.
     *
     * @param Request $request
     * @return view with status or error
     */
    public function storeMysql(Request $request)
    {
        $request->validate($this->rules(), $this->messages());
        try {
            $client = Client::create($request->all() + ['created_by' => 'admin']);
            if ($client) {
                createNewDatabase(database('mysql') . "_{$client->code}");
            }
           
        } catch ( \Exception $e) {
            $client->delete();
            DB::statement("DROP DATABASE " . database('mysql') . "_$client->code");
            Log::info('Exception in '. __CLASS__, ['Exception' => $e]);
        }
    }


    /**
     * postAddClient function.
     *
     * @param Request $request
     * @return view with status or error
     */
    public function storePgsql(Request $request)
    {
        $request->validate($this->rules(), $this->messages());
        try {
            $client = Client::create($request->all() + ['created_by' => 'admin']);
            if ($client) {
                createNewSchema($client->code);
            }
           
        } catch ( \Exception $e) {
            $client->delete();
            DB::statement("DROP SCHEMA $client->code");
            Log::info('Exception in '. __CLASS__, ['Exception' => $e]);
        }
    }


    /**
     * Validation rules to validate incomming request
     *
     * @return array
     */
    public function rules(): array  {

        return [

        ];
    }

    /**
     * Custom validation messages if any
     *
     * @return array
     */
    public function messages(): array  {

        return [

        ];
    }

}