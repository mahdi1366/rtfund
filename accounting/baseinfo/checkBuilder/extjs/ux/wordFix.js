//-------------------- added by Jafarkhani at 89.03 --------------------
Ext.ux.HtmlEditorWordPaste = function() {
  function fixWordPaste(wordPaste) {
    /*wordPaste = wordPaste.replace(/MsoNormal/g, "");
    wordPaste = wordPaste.replace(/<\\?\?xml[^>]*>/g, "");
    wordPaste = wordPaste.replace(/<\/?o:p[^>]*>/g, "");
    wordPaste = wordPaste.replace(/<\/?v:[^>]*>/g, "");
    wordPaste = wordPaste.replace(/<\/?o:[^>]*>/g, "");
    wordPaste = wordPaste.replace(/<\/?st1:[^>]*>/g, "");
    wordPaste = wordPaste.replace(/&nbsp;/g, ""); // <p>&nbsp;</p>
    wordPaste = wordPaste.replace(/<\/?SPAN[^>]*>/g, "");
    wordPaste = wordPaste.replace(/<\/?FONT[^>]*>/g, "");
    wordPaste = wordPaste.replace(/<\/?STRONG[^>]*>/g, "");
    wordPaste = wordPaste.replace(/<\/?H1[^>]*>/g, "");
    wordPaste = wordPaste.replace(/<\/?H2[^>]*>/g, "");
    wordPaste = wordPaste.replace(/<\/?H3[^>]*>/g, "");
    wordPaste = wordPaste.replace(/<\/?H4[^>]*>/g, "");
    wordPaste = wordPaste.replace(/<\/?H5[^>]*>/g, "");
    wordPaste = wordPaste.replace(/<\/?H6[^>]*>/g, "");
    wordPaste = wordPaste.replace(/<\/?P[^>]*><\/P>/g, "");
    wordPaste = wordPaste.replace(/<!--(.*)-->/g, "");
    wordPaste = wordPaste.replace(/<!--(.*)>/g, "");
    wordPaste = wordPaste.replace(/<!(.*)-->/g, "");
    wordPaste = wordPaste.replace(/<\\?\?xml[^>]*>/g, "");
    wordPaste = wordPaste.replace(/<\/?o:p[^>]*>/g, "");
    wordPaste = wordPaste.replace(/<\/?v:[^>]*>/g, "");
    wordPaste = wordPaste.replace(/<\/?o:[^>]*>/g, "");
    wordPaste = wordPaste.replace(/<\/?st1:[^>]*>/g, "");
    wordPaste = wordPaste.replace(/style=\"[^\"]*\"/g, "");
    wordPaste = wordPaste.replace(/style=\'[^\"]*\'/g, "");
    wordPaste = wordPaste.replace(/lang=\"[^\"]*\"/g, "");
    wordPaste = wordPaste.replace(/lang=\'[^\"]*\'/g, "");
    wordPaste = wordPaste.replace(/class=\"[^\"]*\"/g, "");
    wordPaste = wordPaste.replace(/class=\'[^\"]*\'/g, "");
    wordPaste = wordPaste.replace(/type=\"[^\"]*\"/g, "");
    wordPaste = wordPaste.replace(/type=\'[^\"]*\'/g, "");
    wordPaste = wordPaste.replace(/href=\'#[^\"]*\'/g, "");
    wordPaste = wordPaste.replace(/href=\"#[^\"]*\"/g, "");
    wordPaste = wordPaste.replace(/name=\"[^\"]*\"/g, "");
    wordPaste = wordPaste.replace(/name=\'[^\"]*\'/g, "");
    wordPaste = wordPaste.replace(/ clear=\"all\"/g, "");
    wordPaste = wordPaste.replace(/id=\"[^\"]*\"/g, "");
    wordPaste = wordPaste.replace(/title=\"[^\"]*\"/g, "");
    wordPaste = wordPaste.replace(/&nbsp;/g, "");
    wordPaste = wordPaste.replace(/\n/g, "");
    wordPaste = wordPaste.replace(/\r/g, "");
    wordPaste = wordPaste.replace(/<div[^>]*>/g, "<p>");
    wordPaste = wordPaste.replace(/<\/?div[^>]*>/g, "</p>");
    wordPaste = wordPaste.replace(/<span[^>]*>/g, "");
    wordPaste = wordPaste.replace(/<\/?span[^>]*>/g, "");
    wordPaste = wordPaste.replace(/class=/g, "");*/
	
	
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "\"", "&#8704;"); // forall
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "$", "&#8707;"); // exist
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "'", "&#8715;"); // ni
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "@", "&#8773;"); // cong
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "C", "&#935;"); // Chi
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "D", "&#916;"); // Delta
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "F", "&#934;"); // Phi
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "G", "&#915;"); // Gamma
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "J", "&#977;"); // thetasym
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "L", "&#923;"); // Lambda
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "P", "&#928;"); // Pi
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "Q", "&#920;"); // Theta
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "S", "&#931;"); // Sigma
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "V", "&#962;"); // sigmaf
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "U", "&#933;"); // Upsilon
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "W", "&#937;"); // Omega
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "X", "&#926;"); // Xi
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "Y", "&#936;"); // Psi
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "\\", "&#8756;"); // there4
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "^", "&#8869;"); // perp
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "a", "&#945;"); // alpha
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "b", "&#946;"); // beta
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "c", "&#967;"); // chi
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "d", "&#948;"); // delta
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "e", "&#949;"); // epsilon
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "f", "&#934;"); // Phi
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "g", "&#947;"); // gamma
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "h", "&#951;"); // eta
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "i", "&#953;"); // iota
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "j", "&#966;"); // phi
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "k", "&#954;"); // kappa
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "l", "&#955;"); // lambda
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "m", "&#956;"); // mu
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "n", "&#957;"); // nu
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "p", "&#960;"); // pi
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "q", "&#952;"); // theta
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "r", "&#961;"); // rho
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "s", "&#963;"); // sigma 
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "t", "&#964;"); // tau
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "u", "&#965;"); // upsilon
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "v", "&#982;"); // piv
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "w", "&#969;"); // omega
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "x", "&#958;"); // xi
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "y", "&#936;"); // Psi
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "z", "&#950;"); // zeta
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "¡", "&#933;"); // Upsilon
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "¢", "&#180;"); // acute
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "£", "&#8804;"); // le
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "¤", "&#8260;"); // frasl
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "¥", "&#8734;"); // infin
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "¦", "&#402;"); // fnof
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "§", "&#9827;"); // clubs
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "¨", "&#9830;"); // diams
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "©", "&#9829;"); // hearts
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "ª", "&#9824;"); // spades
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "«", "&#8596;"); // harr
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "¬", "&#8592;"); // larr
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "®", "&#8593;"); // uarr
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "¯", "&#8594;"); // rarr
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "²", "&#732;"); // tilde
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "³", "&#8805;"); // ge
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "´", "&#215;"); // times
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "µ", "&#8733;"); // prop
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "¶", "&#8706;"); // part
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "¸", "&#247;"); // divide
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "¹", "&#8800;"); // ne
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "º", "&#8801;"); // equiv
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "»", "&#8776;"); // asymp
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "¼", "&#8230;"); // hellip
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "½", "|"); // 
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "¾", "&#8212;"); // mdash
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "¿", "&#8629;"); // crarr
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "À", "&#8501;"); // alefsym
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "Á", "&#8465;"); // image
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "Â", "&#8476;"); // real
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "Ã", "&#8472;"); // weierp
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "Ä", "&#8855;"); // otimes
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "Å", "&#8853;"); // oplus
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "Æ", "&#8709;"); // empty
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "Ç", "&#8745;"); // cap
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "È", "&#8746;"); // cup
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "É", "&#8835;"); // sup
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "Ê", "&#8839;"); // supe
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "Ë", "&#8836;"); // nsub
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "Ì", "&#8834;"); // sub
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "Í", "&#8838;"); // sube
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "Î", "&#8712;"); // isin
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "Ï", "&#8713;"); // notin
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "Ð", "&#8736;"); // ang
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "â", "&#174;"); // reg
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "ã", "&#169;"); // copy
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "ä", "TM"); // TM
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "Ñ", "&#8711;"); // nabla
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "Ò", "&#174;"); // reg
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "Ó", "&#169;"); // copy
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "Ô", "TM"); // TM
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "Õ", "&#8719;"); // prod
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "Ö", "&#8730;"); // radic
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "×", "."); // 
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "Ø", "&#172;"); // not
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "Ù", "^"); // 
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "Ú", "&#8744;"); // or
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "Û", "&#8660;"); // hArr
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "Ü", "&#8656;"); // lArr
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "Ý", "&#8657;"); //uArr 
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "Þ", "&#8658;"); // rArr
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "ß", "&#8659;"); // dArr
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "à", "&#9674;"); // loz
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "á", "&#9001;"); // lang
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "å", "&#8721;"); // sum
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "ç", "|"); // 
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "æ", "&#8968;"); // lceil
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "è", "&#8970;"); //lfloor 
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "é", "&#8968;"); // lceil
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "ê", "|"); // 
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "ë", "&#8970;"); // lfloor
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "ì", "&#8968;"); // lceil
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "í", "{"); // 
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "î", "&#8970;"); // lfloor
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "ï", "|"); // 
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "ñ", "&#9002;"); // rang
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "ò", "&#8747;"); // int
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "ó", "&#8968;"); // lceil
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "õ", "&#8971;"); // rfloor
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "ô", "|"); // 
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "ö", "&#8969;"); // rceil
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "÷", "|"); // 
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "ø", "&#8971;"); // rfloor
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "ù", "&#8969;"); // rceil
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "ú", "|"); // 
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "û", "&#8971;"); //rfloor 
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "ü", "&#8969;"); // rceil
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "ý", "}"); // 
	wordPaste = this.searchAndReplaceSpecialChars(wordPaste, "þ", "&#8971;"); // rfloor

	return wordPaste;
  }
  
  this.init = function(htmlEditor) {
    this.editor = htmlEditor;
    this.editor.on('render', onRender, this);
  };
  function onRender() {
    this.editor.tb.add('-', {
      itemId: 'wordpaste',
      cls: 'x-btn-icon x-edit-wordpaste',
	  disabled : true,
      handler: function() {
        var wordPaste;
        var wordPasteEditor = new Ext.form.HtmlEditor({
          width: 520,
          height: 150
        });
        var wordPasteWindow = new Ext.Window({ 
          title: "Paste text from Microsoft Word",  
          modal: true,
          width: 537,
          height: 220,
          shadow: true,
          resizable: false,
          plain: true,
          items: wordPasteEditor,
          buttons: [{
            text: 'اصلاح متن',
            handler: function() {
              var wordPaste = wordPasteEditor.getValue();
              wordPaste = fixWordPaste(wordPaste);
              this.editor.focus();
              this.editor.insertAtCursor(wordPaste);
              wordPasteWindow.close();
            },
            scope: this
          }, {
            text: 'انصراف',
            handler: function() {
              wordPasteWindow.close();
            }
          }]
        });
        wordPasteWindow.show();
      },
      scope: this,
      clickEvent: 'mousedown',
      tooltip: '<b>انتقال متن از برنامه Microsoft Word</b><br>متن مورد نظر را copy کرده و در این پنجره paste کنید'
    });
  };
  
	searchAndReplaceSpecialChars = function(src, searchChar, replaceChar)
	{
		var re;
		if(searchChar == "$" || searchChar == "\\" || searchChar == "^")
			re = new RegExp("<span style=\"[^\"]*?font-family: Symbol[^\"]*?\"><span style=\"\">\\" + searchChar + "<\/span>", "g");
		else
			re = new RegExp("<span style=\"[^\"]*?font-family: Symbol[^\"]*?\"><span style=\"\">" + searchChar + "<\/span>", "g");
			
		var index,endIndex,charindex;
		while(true)
		{
			index = src.search(re);
			if(index == -1)
				return src;
				
			endIndex = src.indexOf("<\/span>", index);
			str = src.substr(index, endIndex-index);
			charIndex = str.lastIndexOf(searchChar);
			str = str.substr(0,charIndex) + replaceChar + str.substr(charIndex+1);
			src = src.substr(0,index) + str + src.substr(endIndex);
		}
	};
  
}

//----------------------------------------------------------------------