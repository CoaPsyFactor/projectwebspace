<?php
/**
 * Created by PhpStorm.
 * User: zvekete
 * Date: 21.2.2016.
 * Time: 02.18
 */

namespace App\Modules;

class Response
{


    /** @var Template */
    private $template;

    /** @var array */
    private $headers;

    /** @var array */
    private $cookies;

    /** @var string */
    private $contentType;

    /** @var int */
    private $statusCode;

    /** @var string */
    private $body;

    /** @var string */
    private $additionalBody = '';

    /**
     * Response constructor.
     *
     * @param Template $template
     * @param bool $json
     */
    public function __construct(Template $template, $json = false)
    {
        $this->setTemplate($template);

        $this->setStatusCode(ResponseUtils::CODE_SUCCESS);

        $this->setContentType($json ? ResponseUtils::TYPE_JSON : ResponseUtils::TYPE_HTML);
    }

    /**
     * @param string $path
     * @return $this
     * @throws \App\Exceptions\TemplateException
     */
    public function view($path)
    {
        $this->template->make($path);

        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function data(array $data)
    {
        $this->template->setValues($data);

        return $this;
    }

    /**
     * @return $this
     */
    public function send()
    {
        $this->prepareBody();

        header("Content-Type: {$this->contentType}", true, $this->statusCode);

        echo $this->additionalBody;

        echo $this->body;

        return $this;
    }

    /**
     * @throws \App\Exceptions\TemplateException
     */
    private function prepareBody()
    {
        if (ResponseUtils::TYPE_JSON === $this->contentType) {
            $this->body = json_encode([
                'status' => $this->statusCode,
                'result' => $this->template->getValues()
            ]);
        } else if (ResponseUtils::TYPE_HTML === $this->contentType && $this->template->canRender()) {
            ob_start();

            $this->template->render();

            $this->body = ob_get_clean();
        }
    }

    /**
     * @param string $content
     * @return $this
     */
    public function additionalBodyContent($content)
    {
        $this->additionalBody .= $content;

        return $this;
    }

    /**
     * @param Template $template
     * @return $this
     */
    public function setTemplate(Template $template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @param $code
     * @return $this
     */
    public function setStatusCode($code)
    {
        $this->statusCode = (int) $code;

        return $this;
    }

    /**
     * @param $type
     * @return $this
     */
    public function setContentType($type)
    {
        $this->contentType = $type;

        return $this;
    }

    /**
     * @return $this
     */
    public function asJson()
    {
        $this->setContentType(ResponseUtils::TYPE_JSON);

        return $this;
    }

    /**
     * @return $this
     */
    public function asHtml()
    {
        $this->setContentType(ResponseUtils::TYPE_HTML);

        return $this;
    }

    /**
     * @return $this
     */
    public function asPlain()
    {
        $this->setContentType(ResponseUtils::TYPE_PLAIN);

        return $this;
    }
}