<?php

/**
 * Implements PodsAPI command for WP-CLI
 */
class PodsAPI_Command extends WP_CLI_Command
{

    public $field_types = [
        'text',
        'website',
        'phone',
        'email',
        'password',
        'paragraph',
        'wysiwyg',
        'code',
        'datetime',
        'date',
        'time',
        'number',
        'currency',
        'file',
        'pick',
        'boolean',
        'color'
    ];

    public $input_fields_tmp;

    function last_id()
    {
        return pods_api()->last - id();
    }


    function handle_cmd_inp($msg = "input: ", $default_action = "returning (default)")
    {
        echo $msg . "\n";

        $line = fgets(STDIN);;

        if (trim($line) == '') {
            echo "$line\n";
            echo "No input! $default_action\n";
            return null;
        } else {
            return $line;
        }
    }

    function create_fields($pod_id, $field_args)
    {
        $name = $this->handle_cmd_inp('Enter name for field:');
        $names = [];
        $list_start_index = 1;
        while (isset($name) && $name !== '') {
            $names[] = $name;
            $field = [
                'pod_id' => $pod_id,
                'name' => $name
            ];

            foreach ($field_args as $key => $val) {
                if (is_array($val)) {
                    $msg = "Enter number for $name $key";
                    for ($i = 0; $i < count($val); $i++) {
                        $msg .= "\n" . ($i + $list_start_index) . ": $val[$i]";
                    }
                    $inp = intval($this->handle_cmd_inp($msg));

                    while (!isset($inp) || $inp <= 0 || $inp > count($val)) {
                        echo "out of range";
                        $inp = intval($this->handle_cmd_inp($msg));
                    }

                    // save field
                    $field[$key] = $val[$inp - $list_start_index];

                } else {
                    $inp = $this->handle_cmd_inp("Enter value for $name $key");
                    $field[$key] = $inp;
                }
            };

            // save field
            $this->save_field($field);

            // add another field
            $name = $this->handle_cmd_inp('Enter name for field:');
        }

        return $names;
    }

    /**
     * @subcommand create-custom-post-type
     */

    function create_custom_post_type()
    {
        $arg_pods = [];
        $arg_pods['type'] = 'post_type';
        $arg_pods['name'] = $this->handle_cmd_inp("Enter pod name");
        $pod_id = $this->add_pod(null, $arg_pods);

        $field_args = [
            'type' => $this->field_types
        ];

        $this->create_fields($pod_id, $field_args);
    }

    /**
     * @synopsis --name=<name> --type=<type> --<field>=<value>
     * @subcommand add-pod
     */
    function add_pod($args, $assoc_args)
    {
        if (isset($assoc_args['id']))
            unset($assoc_args['id']);

        $id = pods_api()->save_pod($assoc_args);

        if (0 < $id) {
            WP_CLI::success(__('Pod added', 'pods'));
            WP_CLI::line("ID: {$id}");
        } else
            WP_CLI::error(__('Error adding pod', 'pods'));

        return $id;
    }

    /**
     * @synopsis --<field>=<value>
     * @subcommand save-pod
     */
    function save_pod($args, $assoc_args)
    {
        $id = pods_api()->save_pod($assoc_args);

        if (0 < $id) {
            WP_CLI::success(__('Pod saved', 'pods'));
            WP_CLI::line("ID: {$id}");
        } else
            WP_CLI::error(__('Error saving pod', 'pods'));
    }

    /**
     * @synopsis --<field>=<value>
     * @subcommand duplicate-pod
     */
    function duplicate_pod($args, $assoc_args)
    {
        $id = pods_api()->duplicate_pod($assoc_args);

        if (0 < $id) {
            WP_CLI::success(__('Pod duplicated', 'pods'));
            WP_CLI::line("New ID: {$id}");
        } else
            WP_CLI::error(__('Error duplicating pod', 'pods'));
    }

    /**
     * @synopsis --<field>=<value>
     * @subcommand reset-pod
     */
    function reset_pod($args, $assoc_args)
    {
        $reset = pods_api()->reset_pod($assoc_args);

        if ($reset)
            WP_CLI::success(__('Pod content reset', 'pods'));
        else
            WP_CLI::error(__('Error resetting pod', 'pods'));
    }

    /**
     * @synopsis --<field>=<value>
     * @subcommand delete-pod
     */
    function delete_pod($args, $assoc_args)
    {
        $deleted = pods_api()->delete_pod($assoc_args);

        if ($deleted)
            WP_CLI::success(__('Pod deleted', 'pods'));
        else
            WP_CLI::error(__('Error deleting pod', 'pods'));
    }


    function save_field($params, $table_operation = true, $sanitized = false, $db = true)
    {
//        $params = array(
//            'id' => 0,
//            'pod_id' => 13,
//            'name' => 'test',
//            'label' => 'testlabel',
//            'description' => '',
//            'type' => 'text'
//        );
        $saved = pods_api()->save_field($params, $table_operation, $sanitized, $db);

        if ($saved)
            WP_CLI::success(__('Field saved', 'pods'));
        else
            WP_CLI::error(__('Error saving field', 'pods'));
    }


    /**
     *
     *
     * @synopsis --pod=<pod> --file=<file>
     * @subcommand export-pod
     */
    /*function export_pod ( $args, $assoc_args ) {
        $data = pods_api()->load_pod( array( 'name' => $assoc_args[ 'pod' ] ) );

        if ( !empty( $data ) ) {
            $data = json_encode( $data );

            // @todo write to file
        }

        // @todo success message
    }*/

    /**
     *
     *
     * @synopsis --file=<file>
     * @subcommand import-pod
     */
    /*function import_pod ( $args, $assoc_args ) {
        $data = ''; // @todo get data from file

        $package = array();

        if ( !empty( $data ) )
            $package = @json_decode( $data, true );

        if ( is_array( $package ) && !empty( $package ) ) {
            $api = pods_api();

            if ( isset( $package[ 'id' ] ) )
                unset( $package[ 'id' ] );

            $try = 1;
            $check_name = $package[ 'name' ];

            while ( $api->load_pod( array( 'name' => $check_name, 'table_info' => false ), false ) ) {
                $try++;
                $check_name = $package[ 'name' ] . $try;
            }

            $package[ 'name' ] = $check_name;

            $id = $api->save_pod( $package );

            if ( 0 < $id ) {
                WP_CLI::success( __( 'Pod imported', 'pods' ) );
                WP_CLI::line( "ID: {$id}" );
            }
            else
                WP_CLI::error( __( 'Error importing pod', 'pods' ) );
        }
        else
            WP_CLI::error( __( 'Invalid package, Pod not imported', 'pods' ) );
    }*/


}

WP_CLI::add_command('pods-api', 'PodsAPI_Command');
