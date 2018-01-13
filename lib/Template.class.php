<?php
/*
 *  Copyright notice
 *
 *  (c) 2016 Dirk Friedenberger <archiv10@frittenburger.de>
 *
 *  All rights reserved
 *
 *  This script is part of the Archiv10.PHPRepository project. The PHPRepository is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */
 
class Template
{
    /**
     * Der Ordner in dem sich die Templates befinden.
     *
     * @access    private
     * @var       string
     */
    private $templateDir = "templates/";

    /**
     * Der Ordner in dem sich die Sprach-Dateien befinden.
     *
     * @access    private
     * @var       string
     */
    private $languageDir = "language/";

    /**
     * Der linke Delimter f�r einen Standard-Platzhalter.
     *
     * @access    private
     * @var       string
     */
    private $leftDelimiter = '{$';

    /**
     * Der rechte Delimter f�r einen Standard-Platzhalter.
     *
     * @access    private
     * @var       string
     */
    private $rightDelimiter = '}';

    /**
     * Der linke Delimter f�r eine Funktion.
     *
     * @access    private
     * @var       string
     */
    private $leftDelimiterF = '{';

    /**
     * Der rechte Delimter f�r eine Funktion.
     *
     * @access    private
     * @var       string
     */
    private $rightDelimiterF = '}';

    /**
     * Der linke Delimter f�r ein Kommentar.
     * Sonderzeichen m�ssen escapt werden, weil der Delimter in einem regul�rem
     * Ausdruck verwendet wird.
     *
     * @access    private
     * @var       string
     */
    private $leftDelimiterC = '\{\*';

    /**
     * Der rechte Delimter f�r ein Kommentar.
     * Sonderzeichen m�ssen escapt werden, weil der Delimter in einem regul�rem
     * Ausdruck verwendet wird.
     *
     * @access    private
     * @var       string
     */
    private $rightDelimiterC = '\*\}';

    /**
     * Der linke Delimter f�r eine Sprachvariable
     * Sonderzeichen m�ssen escapt werden, weil der Delimter in einem regul�rem
     * Ausdruck verwendet wird.
     *
     * @access    private
     * @var       string
     */
    private $leftDelimiterL = '\{L_';

    /**
     * Der rechte Delimter f�r eine Sprachvariable
     * Sonderzeichen m�ssen escapt werden, weil der Delimter in einem regul�rem
     * Ausdruck verwendet wird.
     *
     * @access    private
     * @var       string
     */
    private $rightDelimiterL = '\}';

    /**
     * Der komplette Pfad der Templatedatei.
     *
     * @access    private
     * @var       string
     */
    private $templateFile = "";

    /**
     * Der komplette Pfad der Sprachdatei.
     *
     * @access    private
     * @var       string
     */
    private $languageFile = "";

    /**
     * Der Dateiname der Templatedatei.
     *
     * @access    private
     * @var       string
     */
    private $templateName = "";

    /**
     * Der Inhalt des Templates.
     *
     * @access    private
     * @var       string
     */
    private $template = "";


    /**
     * Die Pfade festlegen.
     *
     * @access    public
     */
    public function __construct($tpl_dir = "", $lang_dir = "") {
        // Template Ordner
        if ( !empty($tpl_dir) ) {
            $this->templateDir = $tpl_dir;
        }

        // Sprachdatei Ordner
        if ( !empty($lang_dir) ) {
            $this->languageDir = $lang_dir;
        }
    }

    /**
     * Eine Templatedatei �ffnen.
     *
     * @access    public
     * @param     string $file Dateiname des Templates.
     * @uses      $templateName
     * @uses      $templateFile
     * @uses      $templateDir
     * @uses      parseFunctions()
     * @return    boolean
     */
    public function load($file)    {
        // Eigenschaften zuweisen
        $this->templateName = $file;
        $this->templateFile = $this->templateDir.$file;

        // Wenn ein Dateiname �bergeben wurde, versuchen, die Datei zu �ffnen
        if( !empty($this->templateFile) ) {
            if( file_exists($this->templateFile) ) {
                $this->template = file_get_contents($this->templateFile);
            } else {
                return false;
            }
        } else {
           return false;
        }

        // Funktionen parsen
        $this->parseFunctions();
    }

    /**
     * Einen Standard-Platzhalter ersetzen.
     *
     * @access    public
     * @param     string $replace     Name des Platzhalters.
     * @param     string $replacement Der Text, mit dem der Platzhalter ersetzt
     *                                werden soll.
     * @uses      $leftDelimiter
     * @uses      $rightDelimiter
     * @uses      $template
     */
    public function assign($replace, $replacement) {
        $this->template = str_replace( $this->leftDelimiter .$replace.$this->rightDelimiter,
                                       $replacement, $this->template );
    }

    /**
     * Die Sprachdateien �ffnen und Sprachvariablem im Template ersetzen.
     *
     * @access    public
     * @param     array $files Dateinamen der Sprachdateien.
     * @uses      $languageFiles
     * @uses      $languageDir
     * @uses      replaceLangVars()
     * @return    array
     */
    public function loadLanguage($files) {
        $this->languageFiles = $files;

        // Versuchen, alle Sprachdateien einzubinden
        for( $i = 0; $i < count( $this->languageFiles ); $i++ ) {
            if ( !file_exists( $this->languageDir .$this->languageFiles[$i] ) ) {
                return false;
            } else {
                 include_once( $this->languageDir .$this->languageFiles[$i] );
                 // Jetzt steht das Array $lang zur Verf�gung
            }
        }

        // Die Sprachvariablen mit dem Text ersetzen
        $this->replaceLangVars($lang);

        // $lang zur�ckgeben, damit $lang auch im PHP-Code verwendet werden kann
        return $lang;
    }

    /**
     * Sprachvariablen im Template ersetzen.
     *
     * @access    private
     * @param     string $lang Die Sprachvariablen.
     * @uses      $template
     */
    private function replaceLangVars($lang) {
        $this->template = preg_replace_callback("/\{L_(.*)\}/isU", function ($matches) use ($lang) { 
          $t = strtolower($matches[1]);
		  return "$lang[$t]"; 
		} , $this->template);
    }

    /**
     * Includes parsen und Kommentare aus dem Template entfernen.
     *
     * @access    private
     * @uses      $leftDelimiterF
     * @uses      $rightDelimiterF
     * @uses      $template
     * @uses      $leftDelimiterC
     * @uses      $rightDelimiterC
     */
    private function parseFunctions() {
        // Includes ersetzen ( {include file="..."} )
        while( preg_match( "/" .$this->leftDelimiterF ."include file=\"(.*)\.(.*)\""
                           .$this->rightDelimiterF ."/isUe", $this->template) )
        {
            $this->template = preg_replace_callback( "/" .$this->leftDelimiterF ."include file=\"(.*)\.(.*)\""
                                            .$this->rightDelimiterF."/isU", function ($matches) {												
											    return file_get_contents($this->templateDir.$matches[1].'.'.$matches[2]);
										}, 	$this->template );
        }


        // Kommentare l�schen
        $this->template = preg_replace_callback( "/" .$this->leftDelimiterC ."(.*)" .$this->rightDelimiterC ."/isU",
                                        function ($matches) { return ""; } , $this->template );
    }

    /**
     * Das "fertige Template".
     *
     * @access    public
     * @uses      $template
     */
    public function html() {
        return $this->template;
    }
}
?>
