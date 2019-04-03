<?php



namespace XRA\Extend\Library;

class Resource
{
    public static function checkBlock($row)
    {
        //if($row->lockx == 1 && session_id() != \Auth::user()->session_id){
        return false;
        //}
        //return false;
    }

    public static function blockResource($row)
    {
        //   $table = $row->getTable();

        //      if(!\Schema::hasColumn($table, 'lockx'))
        //      {
        //        \Schema::table($table, function($table){$table->integer('lockx');});
        //      }
        //      //echo '<h3>'.$table.'</h3>';die();
        //      if(!\Schema::hasColumn($table, 'lockx')){
        //        \Schema::table($table, function($table){$table->integer('lockx');});
        //      }
        //      if(!\Schema::hasColumn($table, 'handle')){
        //        \Schema::table($table, function($table){$table->string('handle',50)->nullable();});
        //      }
        //      if(!\Schema::hasColumn($table, 'datemod')){
        //        \Schema::table($table, function($table){$table->datetime('datemod')->nullable();});
        //      }
        //      if(!\Schema::hasColumn($table, 'guid')){
        //        \Schema::table($table, function($table){$table->string('guid',100);});
        //      }

        //      $row->handle = \Auth::user()->nome;
        //      $row->lockx = 1;
        //      $row->datemod = Carbon::now();
        //      $row->update();
    }

    public static function manageResource($request, $row)
    {
        if ('' != $request->input('__submit_cancel')) {
            self::freeResource($row);
            echo SweetAlert::alert('Annullato!', 'Modifiche annullate e risorsa liberata.', 'red');

            return 'show';
        } elseif ('' != $request->input('__submit_exit')) {
            self::freeResource($row);
            echo SweetAlert::alert('Successo!', 'Risorsa salvata e liberata.', 'green');

            return 'show';
        }
        echo SweetAlert::alert('Successo!', 'Risorsa salvata ma ancora BLOCCATA', 'green');

        return 'edit';
    }

    public static function freeResource($row)
    {
        //      $row->lockx = 0;
        //      $row->handle = null;
        //      $row->update();
    }
}
