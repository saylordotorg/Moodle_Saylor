var ZN="3",$N="Any",aO="Aromatic",bO="Nonring",cO="Reset",dO="Ring";function eO(){eO=s;fO=new oo(Wc,new gO)}function gO(){}r(196,193,{},gO);_.Tc=function(a){Yv();yK(this,a.b,hO(a.a.a,a.a.a.ob.selectedIndex))};_.Wc=function(){return fO};var fO;function iO(a,b){if(0>b||b>=a.ob.options.length)throw new Ps;}function hO(a,b){iO(a,b);return a.ob.options[b].value}function jO(){Tt();this.ob=$doc.createElement("select");this.ob[Yc]="gwt-ListBox"}r(341,319,Ah,jO);function kO(){kO=s}
function lO(a,b){if(null==b)throw new Qp("Missing message: awt.103");var c=-1,d,e,f;f=a.mc.a.ob;e=$doc.createElement(nf);e.text=b;e.removeAttribute("bidiwrapped");e.value=b;d=f.options.length;(0>c||c>d)&&(c=d);c==d?f.add(e,null):(c=f.options[c],f.add(e,c))}function mO(){kO();Xv.call(this);new ti;this.mc=new nO((Yv(),this))}r(408,395,{89:1,91:1,98:1,110:1,116:1},mO);_.be=function(){return bw(this.mc,this)};
_.qe=function(){return(null==this.jc&&(this.jc=Jv(this)),this.jc)+va+this.uc+va+this.vc+va+this.rc+Lg+this.hc+(this.qc?l:",hidden")+",current="+hO(this.mc.a,this.mc.a.ob.selectedIndex)};function oO(){XJ.call(this,7)}r(421,1,vh,oO);function pO(a){ZJ.call(this,a,0)}r(426,395,bi,pO);r(552,550,Th);_.Pc=function(){!this.a.Ib?this.a.Ib=new qO(this.a):this.a.Ib.mc.c.gb?mL(this.a.Ib.mc.c):tK(this.a.Ib)};function rO(a,b){iJ(b)==a.a?Z(b,(Hw(),Qw)):Z(b,a.a)}
function sO(a){var b,c,d,e;e=l;d=!1;iJ(tO)!=a.a?(e=ta,d=!0):iJ(uO)!=a.a?(e="!#6",d=!0):iJ(vO)!=a.a?(Z(wO,(Hw(),Qw)),Z(EO,Qw),Z(FO,Qw),Z(GO,Qw),e="F,Cl,Br,I"):(b=iJ(HO)!=a.a,c=iJ(IO)!=a.a,iJ(JO)!=a.a&&(b?e+="c,":c?e+="C,":e+="#6,"),iJ(KO)!=a.a&&(b?e+="n,":c?e+="N,":e+="#7,"),iJ(LO)!=a.a&&(b?e+="o,":c?e+="O,":e+="#8,"),iJ(MO)!=a.a&&(b?e+="s,":c?e+="S,":e+="#16,"),iJ(NO)!=a.a&&(b?e+="p,":c?e+="P,":e+="#15,"),iJ(wO)!=a.a&&(e+="F,"),iJ(EO)!=a.a&&(e+="Cl,"),iJ(FO)!=a.a&&(e+="Br,"),iJ(GO)!=a.a&&(e+="I,"),
IC(e,va)&&(e=e.substr(0,e.length-1-0)),1>e.length&&!a.b&&(b?e=mc:c?e=ob:(Z(tO,(Hw(),Qw)),e=ta)));b=l;d&&iJ(HO)!=a.a&&(b+=";a");d&&iJ(IO)!=a.a&&(b+=";A");iJ(OO)!=a.a&&(b+=";R");iJ(PO)!=a.a&&(b+=";!R");iJ(tO)!=a.a&&0<b.length?e=b.substr(1,b.length-1):e+=b;d=QO.mc.a.ob.selectedIndex;0<d&&(--d,e+=";H"+d);d=RO.mc.a.ob.selectedIndex;0<d&&(--d,e+=";D"+d);iJ(SO)!=a.a&&(e="~");iJ(TO)!=a.a&&(e=eb);iJ(UO)!=a.a&&(e=nb);iJ(VO)!=a.a&&(e="!@");Mx(a.e.mc,e)}
function WO(a){XO(a);YO(a);var b=QO.mc.a;iO(b,0);b.ob.options[0].selected=!0;b=RO.mc.a;iO(b,0);b.ob.options[0].selected=!0;Z(HO,a.a);Z(IO,a.a);Z(OO,a.a);Z(PO,a.a);Z(QO,a.a);Z(RO,a.a);ZO(a)}function XO(a){Z(JO,a.a);Z(KO,a.a);Z(LO,a.a);Z(MO,a.a);Z(NO,a.a);Z(wO,a.a);Z(EO,a.a);Z(FO,a.a);Z(GO,a.a)}function YO(a){Z(tO,a.a);Z(uO,a.a);Z(vO,a.a)}function ZO(a){Z(SO,a.a);Z(TO,a.a);Z(UO,a.a);Z(VO,a.a);a.b=!1}
function qO(a){QJ.call(this,"Atom/Bond Query");this.i=new JJ(this.ig());Aw(this.q,new wK(this));this.a=(Ny(),Py);this.c=a;this.d||(a=Mv(a),this.d=new aK(a),sK(this.d,-150,10));this.j=this.d;kw(this,new oO);Z(this,this.a);a=new pw;kw(a,new gx(0,3,1));$(a,new pO("Atom type :"),null);tO=new JJ($N);uO=new JJ("Any except C");vO=new JJ("Halogen");$(a,tO,null);$(a,uO,null);$(a,vO,null);$(this,a,null);a=new pw;kw(a,new gx(0,3,1));$(a,new ZJ("Or select one or more from the list :",0),null);$(this,a,null);
a=new pw;kw(a,new gx(0,3,1));JO=new JJ(sb);KO=new JJ(Pb);LO=new JJ(Tb);MO=new JJ($b);NO=new JJ(Ub);wO=new JJ(Ab);EO=new JJ(wb);FO=new JJ(rb);GO=new JJ(Ib);$(a,JO,null);$(a,KO,null);$(a,LO,null);$(a,MO,null);$(a,NO,null);$(a,wO,null);$(a,EO,null);$(a,FO,null);$(a,GO,null);$(this,a,null);a=new pw;kw(a,new gx(0,3,1));QO=new mO;lO(QO,$N);lO(QO,Ya);lO(QO,$a);lO(QO,db);lO(QO,ZN);$(a,new pO("Number of hydrogens :  "),null);$(a,QO,null);$(this,a,null);a=new pw;kw(a,new gx(0,3,1));RO=new mO;lO(RO,$N);lO(RO,
Ya);lO(RO,$a);lO(RO,db);lO(RO,ZN);lO(RO,"4");lO(RO,"5");lO(RO,"6");$(a,new ZJ("Number of connections :",0),null);$(a,RO,null);$(a,new ZJ(" (H's don't count.)",0),null);$(this,a,null);a=new pw;kw(a,new gx(0,3,1));$(a,new pO("Atom is :"),null);HO=new JJ(aO);$(a,HO,null);IO=new JJ("Nonaromatic");$(a,IO,null);OO=new JJ(dO);$(a,OO,null);PO=new JJ(bO);$(a,PO,null);$(this,a,null);a=new pw;Z(a,Xw(iJ(this)));kw(a,new gx(0,3,1));$(a,new pO("Bond is :"),null);SO=new JJ($N);$(a,SO,null);TO=new JJ(aO);$(a,TO,
null);UO=new JJ(dO);$(a,UO,null);VO=new JJ(bO);$(a,VO,null);$(this,a,null);a=new pw;kw(a,new gx(1,3,1));this.e=new Lx(ta,20);$(a,this.e,null);$(a,new JJ(cO),null);$(a,this.i,null);$(this,a,null);this.mc&&SJ(this.mc.c,!1);PJ(this,!1);XO(this);YO(this);ZO(this);Z(HO,this.a);Z(IO,this.a);Z(OO,this.a);Z(PO,this.a);Z(QO,this.a);Z(RO,this.a);rO(this,tO);OJ(this);a=this.j;uK(this.mc.c,a.a,a.b);!Lv(this)&&rJ(this);mJ(this)}r(562,544,PF,qO);
_.jg=function(a,b){var c;H(b,cO)?(WO(this),rO(this,tO),sO(this)):E(a.f,88)?(ZO(this),xq(a.f)===xq(tO)?(XO(this),YO(this)):xq(a.f)===xq(uO)?(XO(this),YO(this)):xq(a.f)===xq(vO)?(XO(this),YO(this)):xq(a.f)===xq(OO)?Z(PO,this.a):xq(a.f)===xq(PO)?(Z(OO,this.a),Z(HO,this.a)):xq(a.f)===xq(HO)?(Z(IO,this.a),Z(PO,this.a)):xq(a.f)===xq(IO)?Z(HO,this.a):xq(a.f)===xq(SO)||xq(a.f)===xq(TO)||xq(a.f)===xq(UO)||xq(a.f)===xq(VO)?(WO(this),this.b=!0):YO(this),rO(this,a.f),sO(this)):E(a.f,89)&&(ZO(this),c=a.f,0==c.mc.a.ob.selectedIndex?
Z(c,this.a):Z(c,(Hw(),Qw)),sO(this));107!=this.c.e&&(this.c.e=107,uw(this.c));return!0};_.kg=function(){return Km(this.e.mc.a.ob,Cg)};_.lg=function(){return this.b};_.b=!1;_.c=null;_.d=null;var tO=_.e=null,SO=null,uO=null,HO=null,TO=null,FO=null,JO=null,RO=null,QO=null,EO=null,wO=null,vO=null,GO=null,KO=null,IO=null,PO=null,VO=null,LO=null,NO=null,OO=null,UO=null,MO=null;function nO(a){SE();UE.call(this);this.a=new jO;ws(this.a,new $O(this,a),(eO(),eO(),fO))}r(608,606,{},nO);_.Ke=function(){return this.a};
_.a=null;function $O(a,b){this.a=a;this.b=b}r(609,1,{},$O);_.a=null;_.b=null;Y(562);Y(408);Y(608);Y(609);Y(341);Y(196);y(IF)(3);