<?php
/**
 * Log を http post して IRC に通知
 * @author Keisuke SATO <ksato@otobank.co.jp>
 */
class LogIrcNotice extends Object
{
	/**
	 * @see Log
	 */
	public function flush ($logs, $id, $stdout) {
        foreach ($logs as $log) {
            try {
                $vars = $options = array();
                foreach (module_const_array('params') as $param) {
                    list($name, $value) = array_map('trim', explode(',', $param, 2));
                    $vars[$name] = $value;
                }
                $vars['level'] = $log->fm_level();
                switch ($log->fm_level()) {
                    case 'error':
                    case 'warn':
                    case 'info':
                        $vars['priv'] = 1;
                        $vars['message'] = sprintf("%s:%s %s", pathinfo($log->file(), PATHINFO_FILENAME), $log->line(), str_replace("\n", "", $log->value()));
                        break;
                }
                if (isset($vars['message'])) {
                    $data = http_build_query($vars);

                    $header = array(
                        "Content-Type: application/x-www-form-urlencoded",
                        "Content-Length: ". strlen($data),
                    );

                    $options = array(
                        'http' => array(
                            'method' => 'POST',
                            'header' => implode("\r\n", $header),
                            'content' => $data,
                        ),
                    );
                    file_get_contents(module_const('url'), false, stream_context_create($options));
                }
            } catch (Exception $e) {}
            // print(sprintf('console.%s("[%s:%d]",%s);',$log->fm_level(),$log->file(),$log->line(),str_replace("\n","\\n",Text::to_json($log->value()))));
        }
	}
}
