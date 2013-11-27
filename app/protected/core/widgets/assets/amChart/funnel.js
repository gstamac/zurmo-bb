AmCharts.AmFunnelChart=AmCharts.Class({inherits:AmCharts.AmSlicedChart,construct:function(r){this.chartType="funnel";AmCharts.AmFunnelChart.base.construct.call(this,r);this.startX=this.startY=0;this.baseWidth="100%";this.neckHeight=this.neckWidth=0;this.rotate=!1;this.valueRepresents="height";this.pullDistance=30;this.labelPosition="center";this.labelText="[[title]]: [[value]]";this.balloonText="[[title]]: [[value]]\n[[description]]";AmCharts.applyTheme(this,r,"AmFunnelChart")},drawChart:function(){AmCharts.AmFunnelChart.base.drawChart.call(this);
var r=this.chartData;if(AmCharts.ifArray(r))if(0<this.realWidth&&0<this.realHeight){var s=this.container,A=this.startDuration,k=this.rotate,v=this.updateWidth();this.realWidth=v;var f=this.updateHeight();this.realHeight=f;var n=AmCharts.toCoordinate,B=n(this.marginLeft,v),u=n(this.marginRight,v),a=n(this.marginTop,f)+this.getTitleHeight(),n=n(this.marginBottom,f),u=v-B-u,w=AmCharts.toCoordinate(this.baseWidth,u),p=AmCharts.toCoordinate(this.neckWidth,u),C=f-n-a,x=AmCharts.toCoordinate(this.neckHeight,
C),t=a+C-x;k&&(a=f-n,t=a-C+x);this.firstSliceY=a;AmCharts.VML&&(this.startAlpha=1);for(var g=u/2+B,D=(C-x)/((w-p)/2),y=w/2,w=(C-x)*(w+p)/2+p*x,x=a,F=0,E=0;E<r.length;E++){var c=r[E];if(!0!==c.hidden){var l=[],h=[],b;if("height"==this.valueRepresents)b=C*c.percents/100;else{var m=-w*c.percents/100/2,z=y,d=-1/(2*D);b=Math.pow(z,2)-4*d*m;0>b&&(b=0);b=(Math.sqrt(b)-z)/(2*d);if(!k&&a>=t||k&&a<=t)b=2*-m/p;else if(!k&&a+b>t||k&&a-b<t)d=k?Math.round(b+(a-b-t)):Math.round(b-(a+b-t)),b=d/D,b=d+2*(-m-(z-b/2)*
d)/p}m=y-b/D;z=!1;!k&&a+b>t||k&&a-b<t?(m=p/2,l.push(g-y,g+y,g+m,g+m,g-m,g-m),k?(d=b+(a-b-t),h.push(a,a,a-d,a-b,a-b,a-d,a)):(d=b-(a+b-t),h.push(a,a,a+d,a+b,a+b,a+d,a)),z=!0):(l.push(g-y,g+y,g+m,g-m),k?h.push(a,a,a-b,a-b):h.push(a,a,a+b,a+b));s.set();d=s.set();l=AmCharts.polygon(s,l,h,c.color,c.alpha,this.outlineThickness,this.outlineColor,this.outlineAlpha);d.push(l);this.graphsSet.push(d);c.wedge=d;c.index=E;if(h=this.gradientRatio){var q=[],e;for(e=0;e<h.length;e++)q.push(AmCharts.adjustLuminosity(c.color,
h[e]));0<q.length&&l.gradient("linearGradient",q);c.pattern&&l.pattern(c.pattern)}0<A&&(l=this.startAlpha,this.chartCreated&&(l=c.alpha),d.setAttr("opacity",l));this.addEventListeners(d,c);this.labelsEnabled&&this.labelText&&c.percents>=this.hideLabelsPercent&&(h=this.formatString(this.labelText,c),q=c.labelColor,q||(q=this.color),l=this.labelPosition,e="left","center"==l&&(e="middle"),"left"==l&&(e="right"),h=AmCharts.text(s,h,q,this.fontFamily,this.fontSize,e),d.push(h),q=g,k?(e=a-b/2,c.ty0=e):
(e=a+b/2,c.ty0=e,e<x+F+5&&(e=x+F+5),e>f-n&&(e=f-n)),"right"==l&&(q=u+10+B,c.tx0=g+(y-b/2/D),z&&(c.tx0=g+m)),"left"==l&&(c.tx0=g-(y-b/2/D),z&&(c.tx0=g-m),q=B),c.label=h,c.labelX=q,c.labelY=e,c.labelHeight=h.getBBox().height,h.translate(q,e),(0===c.alpha||0<A&&!this.chartCreated)&&d.hide(),a=k?a-b:a+b,y=m,F=h.getBBox().height,x=e);c.startX=AmCharts.toCoordinate(this.startX,v);c.startY=AmCharts.toCoordinate(this.startY,f);c.pullX=AmCharts.toCoordinate(this.pullDistance,v);c.pullY=0;c.balloonX=g;c.balloonY=
c.ty0}}this.arrangeLabels();this.initialStart();(r=this.legend)&&r.invalidateSize()}else this.cleanChart();this.dispDUpd();this.chartCreated=!0},arrangeLabels:function(){var r=this.rotate,s;s=r?0:this.realHeight;for(var A=0,k=this.chartData,v=k.length,f,n=0;n<v;n++){f=k[v-n-1];var B=f.label,u=f.labelY,a=f.labelX,w=f.labelHeight,p=u;r?s+A+5>u&&(p=s+A+5):u+w+5>s&&(p=s-5-w);s=p;A=w;B.translate(a,p);f.labelY=p;f.tx=a;f.ty=p;f.tx2=a}"center"!=this.labelPosition&&this.drawTicks()}});