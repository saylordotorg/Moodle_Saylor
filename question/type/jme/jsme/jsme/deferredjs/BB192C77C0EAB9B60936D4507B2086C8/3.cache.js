var EZ=" (H's don't count.)",FZ="!#6",GZ="!@",HZ="#15,",IZ="#16,",JZ="#6,",KZ="#7,",LZ="#8,",MZ="3",NZ="4",OZ="5",PZ="6",QZ=";!R",RZ=";A",SZ=";D",TZ=";H",UZ=";R",VZ=";a",WZ="<SELECT>",XZ="Any",YZ="Any except C",ZZ="Aromatic",$Z="Atom is :",a_="Atom type :",b_="Atom/Bond Query",c_="Bond is :",d_="Br,",e_="C,",f_="Cl,",g_="F,",h_="F,Cl,Br,I",i_="Halogen",j_="I,",k_="Missing message: awt.103",l_="N,",m_="Nonaromatic",n_="Nonring",o_="Number of connections :",p_="Number of hydrogens :  ",q_="O,",r_="Or select one or more from the list :",
s_="P,",t_="Reset",u_="Ring",v_="S,",w_="bidiwrapped",x_="c,",y_="gwt-ListBox",z_="n,",A_="o,",B_="p,",C_="s,",D_="~";function E_(){E_=s;F_=new Hx(qj,new G_)}function G_(){}r(193,190,{},G_);_.Pc=function(a){cF();aW(this,a.b,H_(a.a.a,a.a.a.ob.selectedIndex))};_.Sc=function(){return F_};var F_;function I_(a,b){if(0>b||b>=a.ob.options.length)throw new hC;}function H_(a,b){I_(a,b);return a.ob.options[b].value}function J_(){var a;this.ob=(a=WZ,$doc.createElement(a));this.ob[vj]=y_}r(341,319,hr,J_);
function K_(){K_=s}function L_(a,b){if(null==b)throw new hz(k_);var c=-1,d,e,f;f=a.mc.a.ob;e=H(Hn);e.text=b;e.removeAttribute(w_);e.value=b;d=f.options.length;(0>c||c>d)&&(c=d);c==d?f.add(e):(c=f.options[c])?f.add(e,c.index):f.add(e)}function M_(){K_();bF.call(this);new Yr;this.mc=new N_((cF(),this))}r(410,397,{89:1,91:1,98:1,110:1,116:1},M_);_.ae=function(){return gF(this.mc,this)};
_.pe=function(){return(null==this.jc&&(this.jc=OE(this)),this.jc)+ib+this.uc+ib+this.vc+ib+this.rc+qq+this.hc+(this.qc?l:",hidden")+",current="+H_(this.mc.a,this.mc.a.ob.selectedIndex)};function O_(){zV.call(this,7)}r(423,1,dr,O_);function P_(a){BV.call(this,a,0)}r(428,397,Cr,P_);r(554,552,tr);_.Lc=function(){!this.a.Ib?this.a.Ib=new Q_(this.a):this.a.Ib.mc.c.gb?PW(this.a.Ib.mc.c):WV(this.a.Ib)};function R_(a,b){LU(b)==a.a?Z(b,(MF(),VF)):Z(b,a.a)}
function S_(a){var b,c,d,e;e=l;d=!1;LU(T_)!=a.a?(e=gb,d=!0):LU(U_)!=a.a?(e=FZ,d=!0):LU(V_)!=a.a?(Z(W_,(MF(),VF)),Z(X_,VF),Z(Y_,VF),Z(Z_,VF),e=h_):(b=LU($_)!=a.a,c=LU(a0)!=a.a,LU(b0)!=a.a&&(b?e+=x_:c?e+=e_:e+=JZ),LU(c0)!=a.a&&(b?e+=z_:c?e+=l_:e+=KZ),LU(d0)!=a.a&&(b?e+=A_:c?e+=q_:e+=LZ),LU(e0)!=a.a&&(b?e+=C_:c?e+=v_:e+=IZ),LU(f0)!=a.a&&(b?e+=B_:c?e+=s_:e+=HZ),LU(W_)!=a.a&&(e+=g_),LU(X_)!=a.a&&(e+=f_),LU(Y_)!=a.a&&(e+=d_),LU(Z_)!=a.a&&(e+=j_),$L(e,ib)&&(e=e.substr(0,e.length-1-0)),1>e.length&&!a.b&&
(b?e=ti:c?e=Oc:(Z(T_,(MF(),VF)),e=gb)));b=l;d&&LU($_)!=a.a&&(b+=VZ);d&&LU(a0)!=a.a&&(b+=RZ);LU(g0)!=a.a&&(b+=UZ);LU(h0)!=a.a&&(b+=QZ);LU(T_)!=a.a&&0<b.length?e=b.substr(1,b.length-1):e+=b;d=i0.mc.a.ob.selectedIndex;0<d&&(--d,e+=TZ+d);d=j0.mc.a.ob.selectedIndex;0<d&&(--d,e+=SZ+d);LU(k0)!=a.a&&(e=D_);LU(l0)!=a.a&&(e=oc);LU(m0)!=a.a&&(e=Gc);LU(n0)!=a.a&&(e=GZ);SG(a.e.mc,e)}
function o0(a){p0(a);q0(a);var b=i0.mc.a;I_(b,0);b.ob.options[0].selected=!0;b=j0.mc.a;I_(b,0);b.ob.options[0].selected=!0;Z($_,a.a);Z(a0,a.a);Z(g0,a.a);Z(h0,a.a);Z(i0,a.a);Z(j0,a.a);r0(a)}function p0(a){Z(b0,a.a);Z(c0,a.a);Z(d0,a.a);Z(e0,a.a);Z(f0,a.a);Z(W_,a.a);Z(X_,a.a);Z(Y_,a.a);Z(Z_,a.a)}function q0(a){Z(T_,a.a);Z(U_,a.a);Z(V_,a.a)}function r0(a){Z(k0,a.a);Z(l0,a.a);Z(m0,a.a);Z(n0,a.a);a.b=!1}
function Q_(a){sV.call(this,b_);this.i=new lV(this.hg());FF(this.q,new ZV(this));this.a=(dI(),fI);this.c=a;this.d||(a=RE(a),this.d=new DV(a),VV(this.d,-150,10));this.j=this.d;oF(this,new O_);Z(this,this.a);a=new tF;oF(a,new lG(0,3,1));$(a,new P_(a_),null);T_=new lV(XZ);U_=new lV(YZ);V_=new lV(i_);$(a,T_,null);$(a,U_,null);$(a,V_,null);$(this,a,null);a=new tF;oF(a,new lG(0,3,1));$(a,new BV(r_,0),null);$(this,a,null);a=new tF;oF(a,new lG(0,3,1));b0=new lV(gd);c0=new lV(sf);d0=new lV(Kf);e0=new lV(Zf);
f0=new lV(Mf);W_=new lV(he);X_=new lV(Ad);Y_=new lV(Zc);Z_=new lV(se);$(a,b0,null);$(a,c0,null);$(a,d0,null);$(a,e0,null);$(a,f0,null);$(a,W_,null);$(a,X_,null);$(a,Y_,null);$(a,Z_,null);$(this,a,null);a=new tF;oF(a,new lG(0,3,1));i0=new M_;L_(i0,XZ);L_(i0,$b);L_(i0,cc);L_(i0,hc);L_(i0,MZ);$(a,new P_(p_),null);$(a,i0,null);$(this,a,null);a=new tF;oF(a,new lG(0,3,1));j0=new M_;L_(j0,XZ);L_(j0,$b);L_(j0,cc);L_(j0,hc);L_(j0,MZ);L_(j0,NZ);L_(j0,OZ);L_(j0,PZ);$(a,new BV(o_,0),null);$(a,j0,null);$(a,new BV(EZ,
0),null);$(this,a,null);a=new tF;oF(a,new lG(0,3,1));$(a,new P_($Z),null);$_=new lV(ZZ);$(a,$_,null);a0=new lV(m_);$(a,a0,null);g0=new lV(u_);$(a,g0,null);h0=new lV(n_);$(a,h0,null);$(this,a,null);a=new tF;Z(a,bG(LU(this)));oF(a,new lG(0,3,1));$(a,new P_(c_),null);k0=new lV(XZ);$(a,k0,null);l0=new lV(ZZ);$(a,l0,null);m0=new lV(u_);$(a,m0,null);n0=new lV(n_);$(a,n0,null);$(this,a,null);a=new tF;oF(a,new lG(1,3,1));this.e=new RG(gb,20);$(a,this.e,null);$(a,new lV(t_),null);$(a,this.i,null);$(this,a,
null);this.mc&&uV(this.mc.c,!1);rV(this,!1);p0(this);q0(this);r0(this);Z($_,this.a);Z(a0,this.a);Z(g0,this.a);Z(h0,this.a);Z(i0,this.a);Z(j0,this.a);R_(this,T_);qV(this);a=this.j;XV(this.mc.c,a.a,a.b);!QE(this)&&UU(this);PU(this)}r(564,546,jP,Q_);
_.ig=function(a,b){var c;M(b,t_)?(o0(this),R_(this,T_),S_(this)):E(a.f,88)?(r0(this),Pz(a.f)===Pz(T_)?(p0(this),q0(this)):Pz(a.f)===Pz(U_)?(p0(this),q0(this)):Pz(a.f)===Pz(V_)?(p0(this),q0(this)):Pz(a.f)===Pz(g0)?Z(h0,this.a):Pz(a.f)===Pz(h0)?(Z(g0,this.a),Z($_,this.a)):Pz(a.f)===Pz($_)?(Z(a0,this.a),Z(h0,this.a)):Pz(a.f)===Pz(a0)?Z($_,this.a):Pz(a.f)===Pz(k0)||Pz(a.f)===Pz(l0)||Pz(a.f)===Pz(m0)||Pz(a.f)===Pz(n0)?(o0(this),this.b=!0):q0(this),R_(this,a.f),S_(this)):E(a.f,89)&&(r0(this),c=a.f,0==c.mc.a.ob.selectedIndex?
Z(c,this.a):Z(c,(MF(),VF)),S_(this));107!=this.c.e&&(this.c.e=107,yF(this.c));return!0};_.jg=function(){return hw(this.e.mc.a.ob,dq)};_.kg=function(){return this.b};_.b=!1;_.c=null;_.d=null;var T_=_.e=null,k0=null,U_=null,$_=null,l0=null,Y_=null,b0=null,j0=null,i0=null,X_=null,W_=null,V_=null,Z_=null,c0=null,a0=null,h0=null,n0=null,d0=null,f0=null,g0=null,m0=null,e0=null;function N_(a){mO();oO.call(this);this.a=new J_;PB(this.a,new s0(this,a),(E_(),E_(),F_))}r(609,607,{},N_);_.Je=function(){return this.a};
_.a=null;function s0(a,b){this.a=a;this.b=b}r(610,1,{},s0);_.a=null;_.b=null;Y(564);Y(410);Y(609);Y(610);Y(341);Y(193);y(cP)(3);