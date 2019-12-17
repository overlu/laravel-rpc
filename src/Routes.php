<?php

/*Route::any('/overlu/rpc/api',function (\Illuminate\Http\Request $request){
    return (new \Overlu\Rpc\Drivers\ApiModule())->watch($request->input());
});*/

Route::any('/overlu/rpc/api','\Overlu\Rpc\Drivers\ApiModule@watch');
