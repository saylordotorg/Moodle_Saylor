<?php
/*
  Copyright 2006 Wez Furlong, OmniTI Computer Consulting, Inc.
  Based on JLex which is:

       JLEX COPYRIGHT NOTICE, LICENSE, AND DISCLAIMER
  Copyright 1996-2000 by Elliot Joel Berk and C. Scott Ananian

  Permission to use, copy, modify, and distribute this software and its
  documentation for any purpose and without fee is hereby granted,
  provided that the above copyright notice appear in all copies and that
  both the copyright notice and this permission notice and warranty
  disclaimer appear in supporting documentation, and that the name of
  the authors or their employers not be used in advertising or publicity
  pertaining to distribution of the software without specific, written
  prior permission.

  The authors and their employers disclaim all warranties with regard to
  this software, including all implied warranties of merchantability and
  fitness. In no event shall the authors or their employers be liable
  for any special, indirect or consequential damages or any damages
  whatsoever resulting from loss of use, data or profits, whether in an
  action of contract, negligence or other tortious action, arising out
  of or in connection with the use or performance of this software.
  **************************************************************
*/

class JLexToken {
    public $type;
    public $value;
    public $line;
    public $col;
    function __construct($type, $value = null, $line = null, $col = null) {
        $this->type = $type;
        $this->value = $value;
        $this->line = $line;
        $this->col = $col;
    }
}

class JLexBase {
    protected $yy_reader;
    protected $yy_buffer;
    protected $yy_buffer_read = 0;
    protected $yy_buffer_index = 0;
    protected $yy_buffer_start = 0;
    protected $yy_buffer_end = 0;
    protected $yychar = 0;
    protected $yycol = 0;
    protected $yyline = 0;
    protected $yy_at_bol = true;
    protected $yy_lexical_state;
    protected $yy_last_was_cr = false;
    protected $yy_count_lines = false;
    protected $yy_count_chars = false;
    protected $yyfilename = null;
    protected $yy_eof_done = false;

    function __construct($stream) {
        $this->yy_reader = $stream;
        $this->yy_buffer = new qtype_poasquestion\string();

        $meta = stream_get_meta_data($stream);
        if (!isset($meta['uri'])) {
            $this->yyfilename = '<<input>>';
        } else {
            $this->yyfilename = $meta['uri'];
        }
    }

    protected function yybegin($state) {
        $this->yy_lexical_state = $state;
    }

    protected function yy_advance() {
        if ($this->yy_buffer_index < $this->yy_buffer_read) {
            $char = $this->yy_buffer[$this->yy_buffer_index++];
            return core_text::utf8ord($char);
        }
        if ($this->yy_buffer_start != 0) {
            // Shunt.
            $j = $this->yy_buffer_read - $this->yy_buffer_start;
            $this->yy_buffer = $this->yy_buffer->substring($this->yy_buffer_start, $j);
            $this->yy_buffer_end -= $this->yy_buffer_start;
            $this->yy_buffer_start = 0;
            $this->yy_buffer_read = $j;
            $this->yy_buffer_index = $j;

            $data = fread($this->yy_reader, 8192);
            if ($data === false || !core_text::strlen($data)) {
                return $this->YY_EOF;
            }
            $this->yy_buffer->concatenate($data);
            $this->yy_buffer_read += core_text::strlen($data);
        }
        while ($this->yy_buffer_index >= $this->yy_buffer_read) {
            $data = fread($this->yy_reader, 8192);
            if ($data === false || !core_text::strlen($data)) {
                return $this->YY_EOF;
            }
            $this->yy_buffer->concatenate($data);
            $this->yy_buffer_read += core_text::strlen($data);
        }
        $char = $this->yy_buffer[$this->yy_buffer_index++];
        return core_text::utf8ord($char);
    }

    protected function yy_move_end() {
        if ($this->yy_buffer_end > $this->yy_buffer_start && $this->yy_buffer[$this->yy_buffer_end - 1] == "\n") {
            $this->yy_buffer_end--;
        }
        if ($this->yy_buffer_end > $this->yy_buffer_start && $this->yy_buffer[$this->yy_buffer_end - 1] == "\r") {
            $this->yy_buffer_end--;
        }
    }

    protected function yy_mark_start() {
        if ($this->yy_count_lines || $this->yy_count_chars) {
            if ($this->yy_count_lines) {
                for ($i = $this->yy_buffer_start; $i < $this->yy_buffer_index; ++$i) {
                    if ("\n" == $this->yy_buffer[$i] && !$this->yy_last_was_cr) {
                        ++$this->yyline;
                        $this->yycol = 0;
                    }
                    if ("\r" == $this->yy_buffer[$i]) {
                        ++$this->yyline;
                        $this->yycol = 0;
                        $this->yy_last_was_cr = true;
                    } else {
                        $this->yy_last_was_cr = false;
                    }
                }
            }
            if ($this->yy_count_chars) {
                $this->yychar += $this->yy_buffer_index - $this->yy_buffer_start;
                //$this->yycol += $this->yy_buffer_index - $this->yy_buffer_start;
                $i = $this->yy_buffer_index - 1;
                while ($i >= $this->yy_buffer_start &&
                       !($this->yy_buffer[$i] == "\r" ||    // \r
                        ($i > 0 && $this->yy_buffer[$i - 1] != "\r" && $this->yy_buffer[$i] == "\n"))) {   // \n not preceeded by \r
                    $this->yycol++;
                    $i--;
                }
            }
            $this->yy_buffer_start = $this->yy_buffer_index;
        }
    }

    protected function yy_mark_end() {
        $this->yy_buffer_end = $this->yy_buffer_index;
    }

    protected function yy_to_mark() {
        #echo "yy_to_mark: setting buffer index to ", $this->yy_buffer_end, "\n";
        $this->yy_buffer_index = $this->yy_buffer_end;
        $this->yy_at_bol = ($this->yy_buffer_end > $this->yy_buffer_start) &&
                            ("\r" == $this->yy_buffer[$this->yy_buffer_end - 1] ||
                             "\n" == $this->yy_buffer[$this->yy_buffer_end - 1] ||
                             2028 /* unicode LS */ == $this->yy_buffer[$this->yy_buffer_end - 1] ||
                             2029 /* unicode PS */ == $this->yy_buffer[$this->yy_buffer_end - 1]);
    }

    protected function yytext() {
        return $this->yy_buffer->substring($this->yy_buffer_start, $this->yy_buffer_end - $this->yy_buffer_start)->string();
    }

    protected function yylength() {
        return $this->yy_buffer_end - $this->yy_buffer_start;
    }

    static $yy_error_string = array('INTERNAL' => "Error: internal error.\n", 'MATCH' => "Error: Unmatched input.\n");

    protected function yy_error($code, $fatal) {
        print self::$yy_error_string[$code];
        flush();
        if ($fatal) {
            throw new Exception("JLex fatal error " . self::$yy_error_string[$code]);
        }
    }
}
