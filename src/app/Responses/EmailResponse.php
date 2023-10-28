<?php
namespace App\Responses;

use Illuminate\Http\Request;
use PhpMimeMailParser\Parser;
use App\Email;
use App\Http\Resources\EmailResource;

class EmailResponse
{
    protected $request;
    protected $parser;
    protected $acceptedHeaders = [
        'application/vnd.mailcare.v1+json',
        'application/json',
        'text/html',
        'text/plain',
        'message/rfc2822'
    ];

    public function __construct(Request $request, Parser $parser)
    {
        $this->request = $request;
        $this->parser = $parser;
    }

    public function make(Email $email)
    {
        $this->email = $email;

        if ($this->requestPrefer('text/html')) {
            return $this->makeHtml();
        } elseif ($this->requestPrefer('text/plain')) {
            return $this->makeText();
        } elseif ($this->requestPrefer('message/rfc2822')) {
            return $this->makeRaw();
        } elseif ($this->requestPrefer('application/json')) {
            return $this->makeJson();
        } elseif ($this->requestPrefer('application/vnd.mailcare.v1+json')) {
            return $this->makeVersionedJson();
        } else {
            return $this->makeNotAcceptable();
        }
    }

    protected function requestPrefer($contentType)
    {
        $acceptedHeaders = $this->acceptedHeaders;

        if (!$this->email->has_html) {
            $acceptedHeaders = array_diff($acceptedHeaders, array('text/html'));
        }

        if (!$this->email->has_text) {
            $acceptedHeaders = array_diff($acceptedHeaders, array('text/plain'));
        }

        return $contentType == $this->request->prefers($acceptedHeaders);
    }

    protected function makeHtml()
    {
        $this->parser->setPath($this->email->fullPath());

        return response($this->parser->getMessageBody('html'))->header('Content-Type', 'text/html; charset=UTF-8');
    }

    protected function makeText()
    {
        $this->parser->setPath($this->email->fullPath());

        return response($this->parser->getMessageBody('text'))->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    protected function makeRaw()
    {
        return response(
            e(file_get_contents($this->email->fullPath()))
        )->header('Content-Type', 'message/rfc2822; charset=UTF-8');
    }

    protected function makeJson()
    {
        if ($this->email->isUnread()) {
            $this->email->read();
        }

        return new EmailResource($this->email);
    }

    protected function makeVersionedJson()
    {
        if ($this->email->isUnread()) {
            $this->email->read();
        }


        return (new EmailResource($this->email))->response()->header(
            'Content-Type',
            'application/vnd.mailcare.v1+json; charset=UTF-8'
        );
    }

    protected function makeNotAcceptable()
    {
        $acceptableList = implode(",", $this->acceptedHeaders);
        return response()->json([
            'error' => "Not acceptable 'Accept' header. Please use this list: $acceptableList."
            ], 406);
    }
}
