AmCharts.GaugeAxis=AmCharts.Class({construct:function(a){this.radius="95%";this.startAngle=-120;this.endAngle=120;this.startValue=0;this.endValue=200;this.valueInterval=20;this.minorTickInterval=5;this.tickLength=10;this.minorTickLength=5;this.tickColor="#555555";this.labelFrequency=this.tickThickness=this.tickAlpha=1;this.inside=!0;this.labelOffset=15;this.showLastLabel=this.showFirstLabel=!0;this.axisThickness=1;this.axisColor="#000000";this.axisAlpha=1;this.gridInside=!0;this.topText="";this.topTextYOffset=
0;this.topTextBold=!0;this.bottomText="";this.bottomTextYOffset=0;this.bottomTextBold=!0;this.centerY=this.centerX="0%";this.bandOutlineAlpha=this.bandOutlineThickness=0;this.bandOutlineColor="#000000";this.bandAlpha=1;AmCharts.applyTheme(this,a,"GaugeAxis")},value2angle:function(a){return this.startAngle+this.singleValueAngle*a},setTopText:function(a){this.topText=a;var b=this.chart;if(this.axisCreated){this.topTF&&this.topTF.remove();var d=this.topTextFontSize;d||(d=b.fontSize);var c=this.topTextColor;
c||(c=b.color);b=this.chart;a=AmCharts.text(b.container,a,c,b.fontFamily,d,void 0,this.topTextBold);a.translate(this.centerXReal,this.centerYReal-this.radiusReal/2+this.topTextYOffset);this.chart.graphsSet.push(a);this.topTF=a}},setBottomText:function(a){this.bottomText=a;var b=this.chart;if(this.axisCreated){this.bottomTF&&this.bottomTF.remove();var d=this.bottomTextFontSize;d||(d=b.fontSize);var c=this.bottomTextColor;c||(c=b.color);b=this.chart;a=AmCharts.text(b.container,a,c,b.fontFamily,d,void 0,
this.bottomTextBold);a.translate(this.centerXReal,this.centerYReal+this.radiusReal/2+this.bottomTextYOffset);this.bottomTF=a;this.chart.graphsSet.push(a)}},draw:function(){var a=this.chart,b=a.graphsSet,d=this.startValue,c=this.valueInterval,m=this.startAngle,g=this.endAngle,h=this.tickLength,k=(this.endValue-d)/c+1,f=(g-m)/(k-1),n=f/c;this.singleValueAngle=n;var e=a.container,p=this.tickColor,u=this.tickAlpha,B=this.tickThickness,C=c/this.minorTickInterval,D=f/C,E=this.minorTickLength,F=this.labelFrequency,
q=this.radiusReal;this.inside||(q-=15);var w=a.centerX+AmCharts.toCoordinate(this.centerX,a.realWidth),x=a.centerY+AmCharts.toCoordinate(this.centerY,a.realHeight);this.centerXReal=w;this.centerYReal=x;var G={fill:this.axisColor,"fill-opacity":this.axisAlpha,"stroke-width":0,"stroke-opacity":0},l,z;this.gridInside?z=l=q:(l=q-h,z=l+E);var r=this.axisThickness/2,g=AmCharts.wedge(e,w,x,m,g-m,l+r,l+r,l-r,0,G);b.push(g);g=AmCharts.doNothing;AmCharts.isModern||(g=Math.round);G=AmCharts.getDecimals(c);for(l=
0;l<k;l++){var s=d+l*c,r=m+l*f,v=g(w+q*Math.sin(r/180*Math.PI)),t=g(x-q*Math.cos(r/180*Math.PI)),A=g(w+(q-h)*Math.sin(r/180*Math.PI)),y=g(x-(q-h)*Math.cos(r/180*Math.PI)),v=AmCharts.line(e,[v,A],[t,y],p,u,B,0,!1,!1,!0);b.push(v);t=this.labelOffset;this.inside||(t=-t-h);v=w+(q-h-t)*Math.sin(r/180*Math.PI);t=x-(q-h-t)*Math.cos(r/180*Math.PI);A=this.fontSize;isNaN(A)&&(A=a.fontSize);0<F&&l/F==Math.round(l/F)&&(this.showLastLabel||l!=k-1)&&(this.showFirstLabel||0!==l)&&(s=AmCharts.formatNumber(s,a.numberFormatter,
G),s=AmCharts.text(e,s,a.color,a.fontFamily,A),s.translate(v,t),b.push(s));if(l<k-1)for(s=1;s<C;s++)y=r+D*s,v=g(w+z*Math.sin(y/180*Math.PI)),t=g(x-z*Math.cos(y/180*Math.PI)),A=g(w+(z-E)*Math.sin(y/180*Math.PI)),y=g(x-(z-E)*Math.cos(y/180*Math.PI)),v=AmCharts.line(e,[v,A],[t,y],p,u,B,0,!1,!1,!0),b.push(v)}if(b=this.bands)for(d=0;d<b.length;d++)if(c=b[d])p=c.startValue,u=c.endValue,h=AmCharts.toCoordinate(c.radius,q),isNaN(h)&&(h=z),k=AmCharts.toCoordinate(c.innerRadius,q),isNaN(k)&&(k=h-E),f=m+n*p,
u=n*(u-p),B=c.outlineColor,void 0==B&&(B=this.bandOutlineColor),C=c.outlineThickness,isNaN(C)&&(C=this.bandOutlineThickness),D=c.outlineAlpha,isNaN(D)&&(D=this.bandOutlineAlpha),p=c.alpha,isNaN(p)&&(p=this.bandAlpha),c=AmCharts.wedge(e,w,x,f,u,h,h,k,0,{fill:c.color,stroke:B,"stroke-width":C,"stroke-opacity":D}),c.setAttr("opacity",p),a.gridSet.push(c);this.axisCreated=!0;this.setTopText(this.topText);this.setBottomText(this.bottomText);a=a.graphsSet.getBBox();this.width=a.width;this.height=a.height}});AmCharts.GaugeArrow=AmCharts.Class({construct:function(a){this.color="#000000";this.nailAlpha=this.alpha=1;this.startWidth=this.nailRadius=8;this.borderAlpha=1;this.radius="90%";this.nailBorderAlpha=this.innerRadius=0;this.nailBorderThickness=1;this.frame=0;AmCharts.applyTheme(this,a,"GaugeArrow")},setValue:function(a){var b=this.chart;b?b.setValue(this,a):this.previousValue=this.value=a}});AmCharts.GaugeBand=AmCharts.Class({construct:function(){}});AmCharts.AmAngularGauge=AmCharts.Class({inherits:AmCharts.AmChart,construct:function(a){this.theme=a;this.chartType="gauge";AmCharts.AmAngularGauge.base.construct.call(this,a);this.minRadius=this.marginRight=this.marginBottom=this.marginTop=this.marginLeft=10;this.faceColor="#FAFAFA";this.faceAlpha=0;this.faceBorderWidth=1;this.faceBorderColor="#555555";this.faceBorderAlpha=0;this.arrows=[];this.axes=[];this.startDuration=1;this.startEffect=">";this.adjustSize=!0;this.extraHeight=this.extraWidth=
0;this.clockWiseOnly=!1;AmCharts.applyTheme(this,a,"AmAngularGauge")},addAxis:function(a){this.axes.push(a)},formatString:function(a,b){return a=AmCharts.formatValue(a,b,["value"],this.numberFormatter,"",this.usePrefixes,this.prefixesOfSmallNumbers,this.prefixesOfBigNumbers)},initChart:function(){AmCharts.AmAngularGauge.base.initChart.call(this);var a;0===this.axes.length&&(a=new AmCharts.GaugeAxis(this.theme),this.addAxis(a));var b;for(b=0;b<this.axes.length;b++)a=this.axes[b],a=AmCharts.processObject(a,
AmCharts.GaugeAxis,this.theme),a.chart=this,this.axes[b]=a;var d=this.arrows;for(b=0;b<d.length;b++){a=d[b];a=AmCharts.processObject(a,AmCharts.GaugeArrow,this.theme);a.chart=this;d[b]=a;var c=a.axis;AmCharts.isString(c)&&(a.axis=AmCharts.getObjById(this.axes,c));a.axis||(a.axis=this.axes[0]);isNaN(a.value)&&a.setValue(a.axis.startValue);isNaN(a.previousValue)&&(a.previousValue=a.axis.startValue)}this.setLegendData(d);this.drawChart();this.totalFrames=1E3*this.startDuration/AmCharts.updateRate},drawChart:function(){AmCharts.AmAngularGauge.base.drawChart.call(this);
var a=this.container,b=this.updateWidth();this.realWidth=b;var d=this.updateHeight();this.realHeight=d;var c=AmCharts.toCoordinate,m=c(this.marginLeft,b),g=c(this.marginRight,b),h=c(this.marginTop,d)+this.getTitleHeight(),k=c(this.marginBottom,d),f=c(this.radius,b,d),c=b-m-g,n=d-h-k+this.extraHeight;f||(f=Math.min(c,n)/2);f<this.minRadius&&(f=this.minRadius);this.radiusReal=f;this.centerX=(b-m-g)/2+m;this.centerY=(d-h-k)/2+h+this.extraHeight/2;isNaN(this.gaugeX)||(this.centerX=this.gaugeX);isNaN(this.gaugeY)||
(this.centerY=this.gaugeY);var b=this.faceAlpha,d=this.faceBorderAlpha,e;if(0<b||0<d)e=AmCharts.circle(a,f,this.faceColor,b,this.faceBorderWidth,this.faceBorderColor,d,!1),e.translate(this.centerX,this.centerY),e.toBack();for(b=f=a=0;b<this.axes.length;b++)d=this.axes[b],d.radiusReal=AmCharts.toCoordinate(d.radius,this.radiusReal),d.draw(),d.width>a&&(a=d.width),d.height>f&&(f=d.height);(b=this.legend)&&b.invalidateSize();if(this.adjustSize&&!this.chartCreated){e&&(e=e.getBBox(),e.width>a&&(a=e.width),
e.height>f&&(f=e.height));e=0;if(n>f||c>a)e=Math.min(n-f,c-a);0<e&&(this.extraHeight=n-f,this.chartCreated=!0,this.validateNow())}this.dispDUpd();this.chartCreated=!0},validateSize:function(){this.extraHeight=this.extraWidth=0;this.chartCreated=!1;AmCharts.AmAngularGauge.base.validateSize.call(this)},addArrow:function(a){this.arrows.push(a)},removeArrow:function(a){AmCharts.removeFromArray(this.arrows,a);this.validateNow()},removeAxis:function(a){AmCharts.removeFromArray(this.axes,a);this.validateNow()},
drawArrow:function(a,b){a.set&&a.set.remove();var d=this.container;a.set=d.set();if(!a.hidden){var c=a.axis,m=c.radiusReal,g=c.centerXReal,h=c.centerYReal,k=a.startWidth,f=AmCharts.toCoordinate(a.innerRadius,c.radiusReal),n=AmCharts.toCoordinate(a.radius,c.radiusReal);c.inside||(n-=15);var e=a.nailColor;e||(e=a.color);var p=a.nailColor;p||(p=a.color);e=AmCharts.circle(d,a.nailRadius,e,a.nailAlpha,a.nailBorderThickness,e,a.nailBorderAlpha);a.set.push(e);e.translate(g,h);isNaN(n)&&(n=m-c.tickLength);
var c=Math.sin(b/180*Math.PI),m=Math.cos(b/180*Math.PI),e=Math.sin((b+90)/180*Math.PI),u=Math.cos((b+90)/180*Math.PI),d=AmCharts.polygon(d,[g-k/2*e+f*c,g+n*c,g+k/2*e+f*c],[h+k/2*u-f*m,h-n*m,h-k/2*u-f*m],a.color,a.alpha,1,p,a.borderAlpha,void 0,!0);a.set.push(d);this.graphsSet.push(a.set)}},setValue:function(a,b){a.axis&&(a.axis.value2angle(b),a.frame=0,a.previousValue=a.value);a.value=b;var d=this.legend;d&&d.updateValues()},handleLegendEvent:function(a){var b=a.type;a=a.dataItem;if(!this.legend.data&&
a)switch(b){case "hideItem":this.hideArrow(a);break;case "showItem":this.showArrow(a)}},hideArrow:function(a){a.set.hide();a.hidden=!0},showArrow:function(a){a.set.show();a.hidden=!1},updateAnimations:function(){AmCharts.AmAngularGauge.base.updateAnimations.call(this);for(var a=this.arrows.length,b,d=0;d<a;d++){b=this.arrows[d];var c;b.frame>=this.totalFrames?c=b.value:(b.frame++,b.clockWiseOnly&&b.value<b.previousValue&&(c=b.axis,b.previousValue-=c.endValue-c.startValue),c=AmCharts.getEffect(this.startEffect),
c=AmCharts[c](0,b.frame,b.previousValue,b.value-b.previousValue,this.totalFrames),isNaN(c)&&(c=b.value));c=b.axis.value2angle(c);this.drawArrow(b,c)}}});