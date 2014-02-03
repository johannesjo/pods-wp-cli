<?php

/**
 * Implements PodsAPI command for WP-CLI
 */
class PodsAPI_Command extends WP_CLI_Command
{
    public $basic_fields = [
//        [
//            'name' => 'post-title',
//            'type' => 'text'
//        ]
    ];
    public $default_field_args = [
//        'label',
        'type' => [
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
        ],
        'description',
        'required' => [
            true,
            false
        ],
        'unique' => [
            true,
            false
        ],
    ];

    public $tmpl_files = [
        'single' => "tmpl-single.php"
    ];

    function handle_cmd_inp($msg = "input: ", $default_action = "skipping (default)")
    {
        echo "\n" . $msg . "\n";
        $line = trim(fgets(STDIN));
        if (trim($line) == '') {
            echo "$line\n";
            echo "No input! $default_action";
            return null;
        } else {
            return $line;
        }
    }


    function create_tmpl_file($pod_name, $field_names)
    {
        $inp = $this->handle_cmd_inp("Create template files? (yes/no)");
        if ($inp === "yes" || $inp === "y") {
            $base_tmpl = file_get_contents(dirname(__FILE__)
                . '/'
                . $this->tmpl_files['single']);

            // add custom name
            $tmpl = str_replace('pod_name', $pod_name, $base_tmpl);

            // add fields
            $fields = "";
            foreach ($field_names as $field_name) {
                $fields .= "    '$field_name' => $pod_name"
                    . "->field('$field_name'),\n";
            }
            $tmpl = str_replace('/*fields*/', $fields, $tmpl);

            $tmpl_file = "single-$pod_name.php";
            $tmpl_file_path = get_template_directory()
                . '/'
                . $tmpl_file;


            if (!file_exists($tmpl_file_path)) {
                $this->write_file($tmpl_file, $tmpl_file_path, $tmpl);
            } else {
                $inp = $this->handle_cmd_inp("Create template files? (yes/no)");
                if ($inp === "yes" || $inp === "y") {
                    $this->write_file($tmpl_file, $tmpl_file_path, $tmpl);
                }
            }
        }
    }

    function write_file($tmpl_file, $tmpl_file_path, $tmpl)
    {
        // create file
        if (file_put_contents($tmpl_file_path, $tmpl)) {
            echo "Basic template file '$tmpl_file' created in '$tmpl_file_path'\n\n";
        } else {
            echo "An error occured while writing the file.\n";
        }

    }


    function create_basic_fields($pod_id)
    {
        foreach ($this->basic_fields as $field) {
            $field['pod_id'] = $pod_id;
            $this->save_field($field);
            print_r($field);
        }
    }

    /**
     * @param $pod_id
     * @param $field_args
     * @return array
     */

    function create_custom_fields($pod_id, $field_args)
    {
        $name = $this->handle_cmd_inp('Enter name for custom-field (leave empty to continue):');
        $names = [];
        $list_start_index = 1;
        while (isset($name) && $name !== '') {
            $names[] = $name;
            $field = [
                'pod_id' => $pod_id,
                'name' => $name,
                'title' => 'title',

            ];

            foreach ($field_args as $key => $val) {
                // handle fields with options
                if (is_array($val)) {
                    $msg = "Enter number for $name $key";
                    for ($i = 0; $i < count($val); $i++) {
                        // change displayed val
                        if ($val[$i] === true) {
                            $displayed_val = 'true';
                        } else if ($val[$i] === false) {
                            $displayed_val = 'false';
                        } else {
                            $displayed_val = $val[$i];
                        }

                        $msg .= "\n" . ($i + $list_start_index)
                            . ": $displayed_val";
                    }
                    $inp = intval($this->handle_cmd_inp($msg));

                    while (!isset($inp) || $inp <= 0 || $inp > count($val)) {
                        echo "out of range";
                        $inp = intval($this->handle_cmd_inp($msg));
                    }
                    // save field
                    $field[$key] = $val[$inp - $list_start_index];

                } else {
                    // handle other fields
                    $inp = $this->handle_cmd_inp("Enter value for field '$name'-$val (leave empty to skip)");
                    print $val . '  #### ' . $inp;
                    $field[$val] = $inp;
                }
            };
            // save field
            $this->save_field($field);
            echo "Created field '$name'!\n";

            // add another field
            $name = $this->handle_cmd_inp('Enter name for field:');
        }

        return $names;
    }

    /**
     * @subcommand create-custom-post-type
     * @alias cp
     */

    function create_custom_post_type()
    {
        $arg_pods = [];
        $arg_pods['type'] = 'post_type';
        $arg_pods['name'] = $this->handle_cmd_inp("Enter pod name");
        $pod_id = $this->add_pod(null, $arg_pods);

        $this->create_basic_fields($pod_id);
        $field_names = $this->create_custom_fields($pod_id, $this->default_field_args);
        $this->create_tmpl_file($arg_pods['name'], $field_names);
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
        $saved = pods_api()->save_field($params, $table_operation, $sanitized, $db);

        if ($saved)
            WP_CLI::success(__('Field saved', 'pods'));
        else
            WP_CLI::error(__('Error saving field', 'pods'));
    }
}

WP_CLI::add_command('pods-api', 'PodsAPI_Command');
