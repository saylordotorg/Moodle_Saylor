var JN="3",KN="Any",LN="Aromatic",MN="Nonring",NN="Reset",ON="Ring";function PN(){PN=s;QN=new $n(Rc,new RN)}function RN(){}r(194,191,{},RN);_.Pc=function(a){Dv();hK(this,a.b,SN(a.a.a,a.a.a.ob.selectedIndex))};_.Sc=function(){return QN};var QN;function TN(a,b){if(0>b||b>=a.ob.options.length)throw new Es;}function SN(a,b){TN(a,b);return a.ob.options[b].value}function UN(){this.ob=$doc.createElement("select");this.ob[Tc]="gwt-ListBox"}r(342,320,rh,UN);function VN(){VN=s}
function WN(a,b){if(null==b)throw new Bp("Missing message: awt.103");var c=-1,d,e,f;f=a.mc.a.ob;e=$doc.createElement(gf);e.text=b;e.removeAttribute("bidiwrapped");e.value=b;d=f.options.length;(0>c||c>d)&&(c=d);c==d?f.add(e,null):(c=f.options[c],f.add(e,c))}function XN(){VN();Cv.call(this);new ni;this.mc=new YN((Dv(),this))}r(408,395,{89:1,91:1,98:1,110:1,116:1},XN);_.Yd=function(){return Hv(this.mc,this)};
_.le=function(){return(null==this.jc&&(this.jc=ov(this)),this.jc)+va+this.uc+va+this.vc+va+this.rc+Dg+this.hc+(this.qc?l:",hidden")+",current="+SN(this.mc.a,this.mc.a.ob.selectedIndex)};function ZN(){FJ.call(this,7)}r(421,1,nh,ZN);function $N(a){HJ.call(this,a,0)}r(426,395,Sh,$N);r(552,550,Dh);_.Lc=function(){!this.a.Ib?this.a.Ib=new aO(this.a):this.a.Ib.mc.c.gb?WK(this.a.Ib.mc.c):cK(this.a.Ib)};function bO(a,b){RI(b)==a.a?Z(b,(mw(),vw)):Z(b,a.a)}
function cO(a){var b,c,d,e;e=l;d=!1;RI(dO)!=a.a?(e=ta,d=!0):RI(eO)!=a.a?(e="!#6",d=!0):RI(fO)!=a.a?(Z(gO,(mw(),vw)),Z(jO,vw),Z(kO,vw),Z(lO,vw),e="F,Cl,Br,I"):(b=RI(mO)!=a.a,c=RI(nO)!=a.a,RI(oO)!=a.a&&(b?e+="c,":c?e+="C,":e+="#6,"),RI(pO)!=a.a&&(b?e+="n,":c?e+="N,":e+="#7,"),RI(qO)!=a.a&&(b?e+="o,":c?e+="O,":e+="#8,"),RI(rO)!=a.a&&(b?e+="s,":c?e+="S,":e+="#16,"),RI(sO)!=a.a&&(b?e+="p,":c?e+="P,":e+="#15,"),RI(gO)!=a.a&&(e+="F,"),RI(jO)!=a.a&&(e+="Cl,"),RI(kO)!=a.a&&(e+="Br,"),RI(lO)!=a.a&&(e+="I,"),
pC(e,va)&&(e=e.substr(0,e.length-1-0)),1>e.length&&!a.b&&(b?e=lc:c?e=mb:(Z(dO,(mw(),vw)),e=ta)));b=l;d&&RI(mO)!=a.a&&(b+=";a");d&&RI(nO)!=a.a&&(b+=";A");RI(tO)!=a.a&&(b+=";R");RI(uO)!=a.a&&(b+=";!R");RI(dO)!=a.a&&0<b.length?e=b.substr(1,b.length-1):e+=b;d=vO.mc.a.ob.selectedIndex;0<d&&(--d,e+=";H"+d);d=wO.mc.a.ob.selectedIndex;0<d&&(--d,e+=";D"+d);RI(xO)!=a.a&&(e="~");RI(yO)!=a.a&&(e=db);RI(zO)!=a.a&&(e=lb);RI(AO)!=a.a&&(e="!@");KJ(a.e,e)}
function BO(a){CO(a);DO(a);var b=vO.mc.a;TN(b,0);b.ob.options[0].selected=!0;b=wO.mc.a;TN(b,0);b.ob.options[0].selected=!0;Z(mO,a.a);Z(nO,a.a);Z(tO,a.a);Z(uO,a.a);Z(vO,a.a);Z(wO,a.a);EO(a)}function CO(a){Z(oO,a.a);Z(pO,a.a);Z(qO,a.a);Z(rO,a.a);Z(sO,a.a);Z(gO,a.a);Z(jO,a.a);Z(kO,a.a);Z(lO,a.a)}function DO(a){Z(dO,a.a);Z(eO,a.a);Z(fO,a.a)}function EO(a){Z(xO,a.a);Z(yO,a.a);Z(zO,a.a);Z(AO,a.a);a.b=!1}
function aO(a){yJ.call(this,"Atom/Bond Query");this.i=new rJ(this.dg());fw(this.q,new fK(this));this.a=(ty(),vy);this.c=a;this.d||(a=rv(a),this.d=new JJ(a),bK(this.d,-150,10));this.j=this.d;Qv(this,new ZN);Z(this,this.a);a=new Vv;Qv(a,new Mw(0,3,1));$(a,new $N("Atom type :"),null);dO=new rJ(KN);eO=new rJ("Any except C");fO=new rJ("Halogen");$(a,dO,null);$(a,eO,null);$(a,fO,null);$(this,a,null);a=new Vv;Qv(a,new Mw(0,3,1));$(a,new HJ("Or select one or more from the list :",0),null);$(this,a,null);
a=new Vv;Qv(a,new Mw(0,3,1));oO=new rJ(rb);pO=new rJ(Lb);qO=new rJ(Rb);rO=new rJ(Yb);sO=new rJ(Sb);gO=new rJ(Ab);jO=new rJ(vb);kO=new rJ(qb);lO=new rJ(Eb);$(a,oO,null);$(a,pO,null);$(a,qO,null);$(a,rO,null);$(a,sO,null);$(a,gO,null);$(a,jO,null);$(a,kO,null);$(a,lO,null);$(this,a,null);a=new Vv;Qv(a,new Mw(0,3,1));vO=new XN;WN(vO,KN);WN(vO,Ya);WN(vO,$a);WN(vO,cb);WN(vO,JN);$(a,new $N("Number of hydrogens :  "),null);$(a,vO,null);$(this,a,null);a=new Vv;Qv(a,new Mw(0,3,1));wO=new XN;WN(wO,KN);WN(wO,
Ya);WN(wO,$a);WN(wO,cb);WN(wO,JN);WN(wO,"4");WN(wO,"5");WN(wO,"6");$(a,new HJ("Number of connections :",0),null);$(a,wO,null);$(a,new HJ(" (H's don't count.)",0),null);$(this,a,null);a=new Vv;Qv(a,new Mw(0,3,1));$(a,new $N("Atom is :"),null);mO=new rJ(LN);$(a,mO,null);nO=new rJ("Nonaromatic");$(a,nO,null);tO=new rJ(ON);$(a,tO,null);uO=new rJ(MN);$(a,uO,null);$(this,a,null);a=new Vv;Z(a,Cw(RI(this)));Qv(a,new Mw(0,3,1));$(a,new $N("Bond is :"),null);xO=new rJ(KN);$(a,xO,null);yO=new rJ(LN);$(a,yO,
null);zO=new rJ(ON);$(a,zO,null);AO=new rJ(MN);$(a,AO,null);$(this,a,null);a=new Vv;Qv(a,new Mw(1,3,1));this.e=new qx(ta,20);$(a,this.e,null);$(a,new rJ(NN),null);$(a,this.i,null);$(this,a,null);this.mc&&AJ(this.mc.c,!1);xJ(this,!1);CO(this);DO(this);EO(this);Z(mO,this.a);Z(nO,this.a);Z(tO,this.a);Z(uO,this.a);Z(vO,this.a);Z(wO,this.a);bO(this,dO);wJ(this);a=this.j;dK(this.mc.c,a.a,a.b);!qv(this)&&$I(this);VI(this)}r(562,544,vF,aO);
_.eg=function(a,b){var c;H(b,NN)?(BO(this),bO(this,dO),cO(this)):E(a.f,88)?(EO(this),iq(a.f)===iq(dO)?(CO(this),DO(this)):iq(a.f)===iq(eO)?(CO(this),DO(this)):iq(a.f)===iq(fO)?(CO(this),DO(this)):iq(a.f)===iq(tO)?Z(uO,this.a):iq(a.f)===iq(uO)?(Z(tO,this.a),Z(mO,this.a)):iq(a.f)===iq(mO)?(Z(nO,this.a),Z(uO,this.a)):iq(a.f)===iq(nO)?Z(mO,this.a):iq(a.f)===iq(xO)||iq(a.f)===iq(yO)||iq(a.f)===iq(zO)||iq(a.f)===iq(AO)?(BO(this),this.b=!0):DO(this),bO(this,a.f),cO(this)):E(a.f,89)&&(EO(this),c=a.f,0==c.mc.a.ob.selectedIndex?
Z(c,this.a):Z(c,(mw(),vw)),cO(this));107!=this.c.e&&(this.c.e=107,$v(this.c));return!0};_.fg=function(){return wm(this.e.mc.a.ob,ug)};_.gg=function(){return this.b};_.b=!1;_.c=null;_.d=null;var dO=_.e=null,xO=null,eO=null,mO=null,yO=null,kO=null,oO=null,wO=null,vO=null,jO=null,gO=null,fO=null,lO=null,pO=null,nO=null,uO=null,AO=null,qO=null,sO=null,tO=null,zO=null,rO=null;function YN(a){yE();AE.call(this);this.a=new UN;ls(this.a,new FO(this,a),(PN(),PN(),QN))}r(608,606,{},YN);_.Fe=function(){return this.a};
_.a=null;function FO(a,b){this.a=a;this.b=b}r(609,1,{},FO);_.a=null;_.b=null;Y(562);Y(408);Y(608);Y(609);Y(342);Y(194);y(oF)(3);