<?php



class GOOGLE_OP extends baseController
{
    public function getKm($params)
    {
        $from = 'via moglianese 173, peseggia';
        $to = 'via cal di breda 116, treviso';
        \extract($params);
        $from = \urlencode($from);
        $to = \urlencode($to);
        $data = \file_get_contents("http://maps.googleapis.com/maps/api/distancematrix/json?origins=$from&destinations=$to&language=en-EN&sensor=false");
        $data = \json_decode($data);
        $time = 0;
        $distance = 0;
        foreach ($data->rows[0]->elements as $road) {
            $time += $road->duration->value;
            $distance += $road->distance->value;
        }
        /*
        echo "To: ".$data->destination_addresses[0];
        echo "<br/>";
        echo "From: ".$data->origin_addresses[0];
        echo "<br/>";
        echo "Time: ".$time." seconds";
        echo "<br/>";
        echo "Distance: ".$distance." meters";
        */
        $ris = [];
        $ris['to'] = $data->destination_addresses[0];
        $ris['from'] = $data->origin_addresses[0];
        $ris['time_sec'] = $time;
        $ris['meters'] = $distance;

        return $ris;
    }

    public function get_client_ip()
    {
        $ipaddress = '';
        if (\getenv('HTTP_CLIENT_IP')) {
            $ipaddress = \getenv('HTTP_CLIENT_IP');
        } elseif (\getenv('HTTP_X_FORWARDED_FOR')) {
            $ipaddress = \getenv('HTTP_X_FORWARDED_FOR');
        } elseif (\getenv('HTTP_X_FORWARDED')) {
            $ipaddress = \getenv('HTTP_X_FORWARDED');
        } elseif (\getenv('HTTP_FORWARDED_FOR')) {
            $ipaddress = \getenv('HTTP_FORWARDED_FOR');
        } elseif (\getenv('HTTP_FORWARDED')) {
            $ipaddress = \getenv('HTTP_FORWARDED');
        } elseif (\getenv('REMOTE_ADDR')) {
            $ipaddress = \getenv('REMOTE_ADDR');
        } else {
            $ipaddress = 'UNKNOWN';
        }

        return $ipaddress;
    }

    public function getLatLng($params)
    {
        //https://maps.googleapis.com/maps/api/geocode/json?address=via%20moglianese%20peseggia%20173,%20scorze%20venezia
        \extract($params);
        $address = \urlencode($address);
        $data = \file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.$address);
        $data = \json_decode($data);

        return $data;
    }

    //-----------
}//end class
