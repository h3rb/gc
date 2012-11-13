#include <stdio.h>
#include <stdlib.h>
#include <stdbool.h>
#include <string.h>

#include "sqlapi.h"
#include "string.h"
#include "files.h"
#include "mem.h"
#include "config.h"

#define DEBUG 1

#if defined(DEBUG)
int debug_segments=0;
#endif

// Code types used by STRING_LIST
#define TYPE_ANY  0
#define TYPE_PHP  1
#define TYPE_JS   2
#define TYPE_GC_A 3
#define TYPE_GC_B 4
#define IS_GC(type) ( type == TYPE_GC_A || type == TYPE_GC_B )

char *basepath="/gc/language/";

char *header="<?php global $is_gC_site; $is_gC_site=true; require_once($_SERVER['DOCUMENT_ROOT'].'/config.php'); "
			 "require_once($_SERVER['DOCUMENT_ROOT'].'/sites/GC/common/db.php'); "
			 "require_once($_SERVER['DOCUMENT_ROOT'].'/sites/GC/common/security.php'); "
			 "$object=array(); $uses=array(); $_VAR=array(); ?>";

char err_msg[STRING_SIZE];

void php_report( char *msg, char *filename ) {
 char buf[STRING_SIZE];
#if defined(DEBUG)
 printf( "php_report: %s\n", msg );
#endif 
 sprintf( buf, "<?php echo '<html><head><title>Error</title></head><body>%s</body></html>'; ?>", msg );
 string_as_file( buf, filename );
 exit(1);
}

char *disallow_php( char *php_code ) {
 STRING_LIST *sL;
 for ( sL=disallowed_php; sL; sL=sL->next ) {
  if ( stristr(php_code,sL->str) ) {
   char buf[1024];
   sprintf( buf, "%s_IS_DISALLOWED", sL->str );
   php_code=str_replace( php_code, sL->str, buf ); // mem leak
#if defined(DEBUG)
 printf( "disallowed function %s\n", sL->str );
#endif
  }
 }
 return php_code;
}

char *disallow_js( char *js_code ) {
 /*STRING_LIST *sL;
 for ( sL=disallowed; sL; sL=sL->next ) {
  if ( stristr(php_code,sL->str) ) {
   char buf[1024];
   sprintf( buf, "%s_IS_DISALLOWED", sL->str );
   php_code=str_replace( php_code, sL->str, buf ); // mem leak
#if defined(DEBUG)
#endif
  }
 }*/
 return js_code;
}

bool gc_verify( char *source ) {
 int nests=0;
 int double_lbrackets=0;
 int double_rbrackets=0;
 int double_lbracketgt=0;
 int double_rbracketlt=0;
 char *p,*q;
 err_msg[0]='\0';
 double_lbrackets=count_occurrances( source, "[[" );
 double_rbrackets=count_occurrances( source, "]]" )-count_occurrances( source, "]]>" ) /*CDATA tag*/;
 if ( double_lbrackets != double_rbrackets ) {
  sprintf( err_msg, "Mismatched brackets [[,]]: %d [[s versus %d ]]s<br>", double_lbrackets, double_rbrackets );
  return false;
 }
 double_lbracketgt=count_occurrances( source, "[>" );
 double_rbracketlt=count_occurrances( source, "<]" );
 if ( double_lbrackets != double_rbrackets ) {
  sprintf( err_msg, "Mismatched brackets [>,<]: %d [>s versus %d <]s<br>", double_lbracketgt, double_rbracketlt );
  return false;
 }
 p=q=source;
 while ( (p=find_next(p,"[[")) && q!=p ) {
  char *r,*s;
  r=find_next(p+2,"[[");
  s=find_next(p+2, "]]");
  if ( r!=(p+2) && s>r ) {
   sprintf( err_msg, "Nested [[ ]] statements, cannot render.<br>" );
   return false;
  }
  q=p;
 }
/* p=q=source;
 while ( (p=find_next(p,"[>")) && q!=p ) {
  char *r,*s;
  r=find_next(p+2,"[>");
  s=find_next(p+2, "<]");
  if ( r!=(p+2) && s>r ) {
   sprintf( err_msg, "Nested [> <] statements, cannot render.<br>" );
   return false;
  }
  q=p;
 }*/
 p=q=source;
 while ( (p=find_next(p,"[>")) && q!=p ) {
  char *r,*s,*t;
  r=find_next(p+2, "<]");
  s=find_next(p+2, "]]");
  t=find_next(p+2, "[[");
  if ( r!=(p+2) && s<r && t>s ) {
   sprintf( err_msg, "You crossed the streams with [[..[>..]]..<]<br>" );
   return false;
  }
  q=p;
 }
 p=q=source;
 while ( (p=find_next(p,"[[")) && q!=p ) {
  char *r,*s,*t;
  r=find_next(p+2, "]]");
  s=find_next(p+2, "<]");
  t=find_next(p+2, "[>");
  if ( r!=(p+2) && s<r && t>s ) {
   sprintf( err_msg, "You crossed the streams with [>..[[..<]..]]<br>" );
   return false;
  }
  q=p;
 }
 return true;
}

STRING_LIST *segment( char *mixed, char **p, STRING_LIST *segments, char *dest ) {
 int type;
 if ( (**p)=='\0' || *((*p)+1)== '\0' ) {
  char *end=*p;
  if ( (**p)== '\0' ) return segments;
  STRING_LIST *sL=new_string_list();
  sL->type=TYPE_ANY;
  sL->str=gsubstr(mixed,*p,(*p)+1);
  segments=push_sl_list( sL, segments );
#if defined(DEBUG)
 printf( "Code segment #%d is type %d:\n>>>>>>\n%s\n<<<<<<\n", debug_segments++, segments->type, segments->str );
#endif
  *p=end;
 } else
/* if ( (**p)=='<' && *((*p)+1)== 's' && *((*p)+2)== 'c' && *((*p)+3)== 'r' && *((*p)+4)== 'i' ) {
  char *end=find_next(*p,"</script>");
  char *next=find_next(*p,"<script");
  if ( end == *p ) {
   while ( *end != '\0' ) end++;
  } else
  if ( next == *p || end<next ) { end+=9; }
  else {
   php_report( "Nested <script></script> tags; page may cause undefined behavior.", dest );
   exit(1);
  }
  STRING_LIST *sL=new_string_list();
  sL->type=TYPE_JS;
  sL->str=gsubstr(mixed,*p,end);
  segments=push_sl_list( sL, segments );
#if defined(DEBUG)
 printf( "Code segment #%d is Javascript:\n>>>>>>\n%s\n<<<<<<\n", debug_segments++, segments->str );
#endif
  *p=end;
 } else */
 if ( (**p)== '<' && *((*p)+1)== '?' ) {
  char *start=*p;
  char *end=find_next(*p,"?>");
  if ( end == start ) while (*end != '\0') end++;
  else end+=2;
  STRING_LIST *sL=new_string_list();
  sL->type=TYPE_PHP;
  sL->str=gsubstr(mixed,start,end);
  segments=push_sl_list( sL, segments );
#if defined(DEBUG)
 printf( "Code segment #%d is PHP:\n>>>>>>\n%s\n<<<<<<\n", debug_segments++, segments->str );
#endif
  *p=end;
 } else
 if ( (**p)== '[' && *((*p)+1)== '[' ) {
  char *end=find_next(*p,"]]");
  if ( end == *p ) while (*end != '\0') end++;
  else end+=2;
  STRING_LIST *sL=new_string_list();
  sL->type=TYPE_GC_A;
  sL->str=gsubstr(mixed,*p,end);
  segments=push_sl_list( sL, segments );
#if defined(DEBUG)
 printf( "Code segment #%d is Gudacode:\n>>>>>>\n%s\n<<<<<<\n", debug_segments++, segments->str );
#endif
  *p=end;
 } else
 if ( (**p)== '[' && *((*p)+1)== '>' ) {
  STRING_LIST *sL=new_string_list();
  char *end=find_next(*p,"<]");
  if ( end == *p ) while (*end != '\0') end++;
  else {
   char *start=find_next((*p)+2,"[>");
   int starts=0;
   if ( start != (*p)+2 ) {
    if ( start < end ) starts++;
    while ( start < end ) {
     char *next=find_next(start+2,"[>");
     if ( next < end && next!=(start+2) ) starts++;
     if ( next==(start+2) || next > end ) break;
     start=next;
     starts++;
    }
   }
   printf ( "starts: %d\n", starts );
   while ( starts > 0 ) {
    end=find_next(end+2,"<]");
    starts--;
   }
   if ( *end == '<' ) { end++; if ( *end == ']' ) end++; }
  }
  sL->type=TYPE_GC_B;
  sL->str=gsubstr(mixed,*p,end);
  segments=push_sl_list( sL, segments );
#if defined(DEBUG)
 printf( "Code segment #%d is Gudacode iterator:\n>>>>>>\n%s\n<<<<<<\n", debug_segments++, segments->str );
#endif
  *p=end;
 } else {
  char *end=*p;
  char *q,*r,*s,*t,*u;
  q=find_next(*p,"<script");
  r=find_next(*p,"<?php");
  s=find_next(*p,"[[");
  t=find_next(*p,"[>");
  u=*p;
  while ( *u != '\0' ) u++;
  if ( q==*p && r==*p && s==*p && t==*p ) end=u; else
  if ( q!=*p && (r==*p||q<r) && (s==*p||q<s) && (t==*p||q<t) ) end=q; else
  if ( r!=*p && (q==*p||r<q) && (s==*p||r<s) && (t==*p||r<t) ) end=r; else
  if ( s!=*p && (q==*p||s<q) && (r==*p||s<r) && (t==*p||s<t) ) end=s; else
  if ( t!=*p && (q==*p||t<q) && (r==*p||t<r) && (s==*p||t<s) ) end=t; 
  STRING_LIST *sL=new_string_list();
  sL->type=TYPE_ANY;
  sL->str=gsubstr(mixed,*p,end);
  segments=push_sl_list( sL, segments );
#if defined(DEBUG)
 printf( "Code segment #%d is mixed-mode XML/HTML:\n>>>>>>\n%s\n<<<<<<\n", debug_segments++, segments->str );
#endif
  *p=end;
 }
 return segments;
}

// gives rudimentary segmentation, does not handle nested php
STRING_LIST *code_slice( char *mixed, char *dest ) {
 STRING_LIST *segments=NULL;
 char *p=mixed; 
 if ( *p == '<' && *(p+1)=='?' && LOWER(*(p+2))=='p' && LOWER(*(p+3))=='h' && LOWER(*(p+4))=='p' ) {
  char *end=find_next(p,"?>");
  if ( end == p ) while (*end != '\0') end++;
  else end+=2;
  STRING_LIST *sL=new_string_list();
  sL->type=TYPE_PHP;
  sL->str=gsubstr(mixed,p,end);
  segments=push_sl_list( sL, segments );
#if defined(DEBUG)
 printf( "Code segment #%d is PHP:\n>>>>>>\n%s\n<<<<<<\n", debug_segments++, segments->str );
#endif
  p=end;
 } else
 if ( *p == '[' && *(p+1) == '[' ) {
  char *end=find_next(p,"]]");
  if ( end == p ) while (*end != '\0') end++;
  else end+=2;
  STRING_LIST *sL=new_string_list();
  sL->type=TYPE_GC_A;
  sL->str=gsubstr(mixed,p,end);
  segments=push_sl_list( sL, segments );
#if defined(DEBUG)
 printf( "Code segment #%d is Gudacode:\n>>>>>>\n%s\n<<<<<<\n", debug_segments++, segments->str );
#endif
  p=end;
 } else
 if ( *p == '[' && *(p+1) == '>' ) {
  int starts=0;
  char *start=find_next(p+2,"[>");
  char *end=find_next(p,"<]");
  STRING_LIST *sL=new_string_list();
  while ( start<end ) { starts++; start=find_next(start+2,"[>"); }
  while ( starts > 0 ) {
   starts--;
   end=find_next(end+2,"<]");
   if ( end==(end+2) ) {
    while ( *end!='\0' ) end++;
    starts=0;
   }
  }
  if ( end == p && *end != '\0' ) while (*end != '\0') end++;
  else end+=2;
  sL->type=TYPE_GC_B;
  sL->str=gsubstr(mixed,p,end);
  segments=push_sl_list( sL, segments );
#if defined(DEBUG)
 printf( "Code segment #%d is Gudacode iterator:\n>>>>>>\n%s\n<<<<<<\n", debug_segments++, segments->str );
#endif
  p=end;
 } else {
  char *q,*r,*s,*t;
  q=find_next(p,"<script");
  r=find_next(p,"<?php");
  s=find_next(p,"[[");
  t=find_next(p,"[>");
  if ( q!=p && (r==p||q<r) && (s==p||q<s) && (t==p||q<t) ) p=q; else
  if ( r!=p && (q==p||r<q) && (s==p||r<s) && (t==p||r<t) ) p=r; else
  if ( s!=p && (q==p||s<q) && (r==p||s<r) && (t==p||s<t) ) p=s; else
  if ( t!=p && (q==p||t<q) && (r==p||t<r) && (s==p||t<s) ) p=t; 
  if ( p!=mixed ) {
   STRING_LIST *sL=new_string_list();
   sL->str=gsubstr(mixed,mixed,p);
   sL->type=TYPE_ANY;
   segments=push_sl_list( sL, segments );
#if defined(DEBUG)
 printf( "Code segment #%d is XML/HTML:\n>>>>>>\n%s\n<<<<<<\n", debug_segments++, segments->str );
#endif
  }
 }
 while ( *p != '\0' /*&& strlen(p) > 7*/ ) {
  segments=segment(mixed, &p,segments, dest);
  }
#if defined(DEBUG)
 printf( "Total: %d level 1 segments\n", strings_in_list(segments) );
#endif
 return segments;
}

char *html_script_tag_normalize( char *source ) {
 return source;
}

char *gc_a( char *a, char *dest ) {
 char *b;
 char *code;
 char *final;
 char *e=a;
 STRING_LIST *object_path=NULL, *o;
 char object[1024];
 char codepath[STRING_SIZE];
 char variables[STRING_SIZE];
 char uses[STRING_SIZE];
 int err=0;
#if defined(DEBUG)
 printf( "gc_a: %s\n", a );
#endif  
 sprintf ( variables, "<?php " );
 sprintf ( uses, "<?php " );
 while ( *a=='[' ) a++;
 while ( *e!='\0' ) e++; 
 e--;
 while ( *e== ']' ) e--;
 e++;
 *e='\0';
 a=first_word(a,object);
 object[0]=UPPER(object[0]);
#if defined(DEBUG)
 printf( "gc_a: first word: %s\n", object );
#endif  
 if ( !str_cmp( object, "Uses" ) ) {
  while ( *a!='\0' ) {
   char *v;
   char param[1024];
   a=uses_appositive(a,param);
   v=param;
#if defined(DEBUG)
 printf( "gc_a: Uses, object: %s\n", param );
#endif 
   if ( contains( param, '(' ) ) {
    char obj[STRING_SIZE];
	char id[STRING_SIZE];
	char parent[STRING_SIZE];
	char *p;
	char *u;
	int i=0;
	p=param;
	while ( *p != '(' ) { obj[i++]=*p; p++; }
	obj[i]='\0';
	obj[0]=UPPER(obj[0]);
	i=0;
	while ( *p != ')' ) { id[i++]=*p; p++; }
	v=p+1;
	id[i]='\0';
	p=obj;
	while ( *p != '\0' ) {
	 if ( *p == '_' ) *p='/';
	 p++;
	}
	sprintf( codepath, "%s%s/uses.gc", basepath, obj );
	if ( !file_exists( codepath ) ) {
	 p=obj;
	 while ( *p != '\0' ) {
	  if ( *p == '/' ) *p='_'; 
	  p++;
  	 }
	 sprintf( err_msg, "Uses \"%s\" no such object<br>", obj );
	 php_report( err_msg, dest );
	}
	err=file_as_string( codepath, &u );
	if ( err == 0 ) {
	 sprintf( err_msg, "Uses \"%s\" no such object<br>", obj );
	 php_report( err_msg, dest );
	}	
	if ( strlen(id) > 0 ) scat( uses, "$uses['%s']=\"%s\"; ?>%s<?php ", obj, id, u );
	else scat( uses, "$uses['%s']=NULL; unset($uses['%s']); ?>%s<?php ", obj, u );
	free(u);
   }
   while ( *v != '\0' ) {
    if ( contains( v, '=' ) ) { // <obj> <name> <var=val>
     char variable[1024];
  	 char value[1024];
	 char *p;
	 char cEnd=' ';
	 int i=0;
#if defined(DEBUG)
 printf( "gc_a: uses %s contains is an assignment\n", object );
#endif   	
	 p=v;
	 while ( *p != '=' ) { variable[i++]=*p; p++; }
	 variable[i]='\0';
	 while ( isspace(*p) || *p=='=' ) p++;
	 if ( *p=='"' ) { cEnd='"'; p++; }
	 i=0;
	 while( *p != cEnd ) { value[i++]=*p; p++; }
	 value[i]='\0';
	 if ( *p=='"' ) p++;
	 if ( is_number( value ) )
	 scat( uses, "$_VAR['%s']=%s; ", variable, value );
         else
         if ( strlen(value) == 0 ) 
         scat( uses, "$_VAR['%s']=true; ", variable );
	 else
	 scat( uses, "$_VAR['%s']=\"%s\"; ", variable, value );
	 v=p;
    } else { // <obj> <name> <var>
         char variable[1024];
 	 v=first_word(v,variable);
 	 scat( uses, "$_VAR['%s']=\"on\"; ", variable);
    }
   }
  }
  if ( *a==';' ) {
   char *g=gc_a(a+1,dest);
   scat( uses, "?>%s<?php ", g );
   free(g);
//   break;
  } 
 } else {
  while ( *a != '\0' ) {
   char param[1024];
   char Param[1024];
   int NotASubObject=0;
   first_word(a,param);
   a=first_Arg(a,Param);
#if defined(DEBUG)
 printf( "gc_a: object param: %s\n", param );
#endif  
   if ( !str_cmp( param, "as" ) 
      ||!str_cmp( param, "by" )
	  ||!str_cmp( param, "in" )
	  ||!str_cmp( param, "with" )
	  ||!str_cmp( param, "of" )
	  ||!str_cmp( param, "the" )
	  ||!str_cmp( param, "to" )
	  ||!str_cmp( param, "from" ) ) continue;
   if ( contains(param, '=') || NotASubObject==1 ) {
	char variable[1024];
	char value[1024];
	char *p,*q;
	char cEnd=' ';
	int i=0;
        NotASubObject=1;
#if defined(DEBUG)
 printf( "gc_a: param is an assignment\n" );
#endif
	p=param;
        q=Param;
	while ( *p != '=' && *p!='\0' ) { if ( *p != ' ' ) variable[i++]=*p; p++; q++; }
	variable[i]='\0';
	while ( isspace(*p) || *p=='=' ) { p++; q++; }
        printf( "Var: %s\n", variable );
	if ( *p=='"' ) { p++; q++; cEnd='"'; }
	i=0;
	while( *p != cEnd && *p!='\0') { value[i++]=*q; q++; p++; }
	value[i]='\0';
        printf( "Value: %s\n", value );
	if ( *p=='"' ) { p++; q++; }
	if ( is_number( value ) )
	scat( variables, "$_VAR['%s']=%s; ", variable, value );
	else
	scat( variables, "$_VAR['%s']=\"%s\"; ", variable, value );
   } else {
     STRING_LIST *sL=new_string_list();
     sL->str=str_dup(param);
#if defined(DEBUG)
 printf( "gc_a: param added to object path: %s\n", sL->str );
#endif
	object_path=push_sl_list( sL, object_path );
   }
  }
 }
 sprintf( codepath, "%s%s/", basepath, object );
 object_path=rev_string_list(object_path);
 for ( o=object_path; o; o=o->next ) scat( codepath, "%s/", o->str );
 scat( codepath, "function.gc" );
#if defined(DEBUG)
 printf( "gc_a: looking for file: %s\n", codepath );
#endif
 if ( !file_exists( codepath ) ) {
#if defined(DEBUG)
 printf( "gc_a: file not found\n" );
#endif
  sprintf( codepath, "%s%s", object, object_path?" ":"" );
  for ( o=object_path; o; o=o->next ) scat( codepath, "%s%s", o->str, (o->next ? " " : "") );
  sprintf( err_msg, "\"%s\": No such object<br>", codepath );
  php_report( err_msg, dest );
 }
 err=file_as_string( codepath, &code );
 if ( err == 0 ) {
  sprintf( codepath, "%s%s", object, object_path?" ":"" );
  for ( o=object_path; o; o=o->next ) scat( codepath, "%s%s", o->str, (o->next ? " " : "") );
  sprintf( err_msg, "\"%s\": No such object<br>", codepath );
  php_report( err_msg, dest );
 }
 scat( uses, " ?>" );
 scat( variables, " ?>" );
 final=calloc((strlen(uses)+strlen(variables)+strlen(code)+1),sizeof(char));
 sprintf( final, "%s%s%s", uses,variables,code );
 free_string_list( object_path );
#if defined(DEBUG)
 printf( "gc_a: %s\n", final );
#endif 
 return final;
}

char *gc_b( char *b, char *dest ) {
 char *a, *c=b, *e=b,*f;
 char *escaped;
 char *c0de;
 int err;
 char *code;
 char codepath[STRING_SIZE];
 char parent[STRING_SIZE];
 char variables[STRING_SIZE];
 char *final;
 STRING_LIST *object_path=NULL, *o;
#if defined(DEBUG)
 printf( "gc_b: %s\n", b );
#endif  
 if ( *c == '[' ) c++;
 if ( *c == '>' ) c++;
 e=c;
 while ( *e != '\0' ) e++;
 if ( *e=='\0' ) e--;
 if ( *e==']' && *(e-1) == '<' ) e-=2;
 f=c;
 while ( *f != ':' && f != e ){
  if ( *f=='"' ) { f++; while ( *f!='"' && f!=e && *f!='\0' ) f++; }
  f++;
 }
 a=unpad(gsubstr(c,c,f));
 c0de=gsubstr(c,f+1,e);
 a=first_word(a,parent);
 parent[0]=UPPER(parent[0]);
 while ( *a != '\0' ) {
   char param[1024];
   char Param[1024];
   int NotASubObject=0;
   first_word(a,param);
   a=first_Arg(a,Param);
#if defined(DEBUG)
 printf( "gc_b: object param: %s\n", param );
#endif
   if ( !str_cmp( param, "as" ) ||!str_cmp( param, "by" ) ||!str_cmp( param, "in" ) ||!str_cmp( param, "with" )
      ||!str_cmp( param, "of" ) ||!str_cmp( param, "the" ) ||!str_cmp( param, "to" ) ||!str_cmp( param, "from" ) ) continue;
   if ( contains(param, '=') || NotASubObject==1 ) {
	char variable[1024];
	char value[1024];
	char *p,*q;
	char cEnd=' ';
	int i=0;
        NotASubObject=1;
#if defined(DEBUG)
 printf( "gc_b: param is an assignment\n" );
#endif
	p=param;
        q=Param;
	while ( *p != '=' && *p!='\0' ) { if ( *p != ' ' ) variable[i++]=*p; p++; q++; }
	variable[i]='\0';
	while ( isspace(*p) || *p=='=' ) { p++; q++; }
        printf( "Var: %s\n", variable );
	if ( *p=='"' ) { p++; q++; cEnd='"'; }
	i=0;
	while( *p != cEnd && *p!='\0') { value[i++]=*q; q++; p++; }
	value[i]='\0';
        printf( "Value: %s\n", value );
	if ( *p=='"' ) { p++; q++; }
	if ( is_number( value ) )
	scat( variables, "$_VAR['%s']=%s; ", variable, value );
	else
	scat( variables, "$_VAR['%s']=\"%s\"; ", variable, value );
   } else {
     STRING_LIST *sL=new_string_list();
     sL->str=str_dup(param);
     object_path=push_sl_list(sL,object_path);
   }
 }
 sprintf( codepath, "%s%s/", basepath, parent );
 object_path=rev_string_list(object_path);
 for ( o=object_path; o; o=o->next ) scat( codepath, "%s/", o->str );
 scat( codepath, "iterator.gc" );
#if defined(DEBUG)
 printf( "gc_b: looking for file: %s\n", codepath );
#endif
 if ( !file_exists( codepath ) ) {
#if defined(DEBUG)
 printf( "gc_b: file not found\n" );
#endif
  sprintf( codepath, "%s%s", parent, object_path?" ":"" );
  for ( o=object_path; o; o=o->next ) scat( codepath, "%s%s", o->str, (o->next ? " " : "") );
  sprintf( err_msg, "\"%s\": No such iterator<br>", codepath );
  php_report( err_msg, dest );
 }
 err=file_as_string( codepath, &code );
 if ( err == 0 ) {
  sprintf( codepath, "%s%s", parent, object_path?" ":"" );
  for ( o=object_path; o; o=o->next ) scat( codepath, "%s%s", o->str, (o->next ? " " : "") );
  sprintf( err_msg, "\"%s\": No such object, or object is not an iterator<br>", codepath );
  php_report( err_msg, dest );
 }
 escaped=addsq(c0de);
 final=calloc(6+strlen(variables)+9+strlen(escaped)+2+3+strlen(code)+1,sizeof(char));
 sprintf( final, "<?php %s $_CODE='%s'; ?>%s", variables,escaped,code );
 free_string_list(object_path);
 free(c0de);
 free(escaped);
#if defined(DEBUG)
 printf( "gc_b: %s\n", final );
#endif 
 return final;
}

STRING_LIST *crunch( STRING_LIST *sliced, char *dest ) {
    STRING_LIST *output;
    STRING_LIST *sL=new_string_list();
	sL->str=str_dup(header);
	sL->type=TYPE_ANY;
	output=push_sl_list(sL,output);
    for ( sL=sliced; sL; sL=sL->next ) switch ( sL->type ) {
	 case TYPE_ANY:
	  {
	   STRING_LIST *out=new_string_list();
	   out->str=sL->str;
	   output=push_sl_list(out,output);
	  }
	 break;
	 case TYPE_PHP:
	  {
          // Disallow php
	   STRING_LIST *sliced_php,*sp;
	   char *b;
 	   sL->str=disallow_php(sL->str);
	   b=sL->str;
	   while ( *b!='\0' && *b != '?' && *(b+1) != '>' ) b++;
	   *b='\0';
	   STRING_LIST *out=new_string_list();
	   out->str=sL->str+5; sL->str=NULL;
	   output=push_sl_list(out,output);
	  }
	 break;
	 case TYPE_JS:
	  {
 	  // Disallow Javascript
	   STRING_LIST *out=new_string_list();
	   out->str=sL->str; sL->str=NULL;
	   output=push_sl_list(out,output);
	  }
	 break;
	 case TYPE_GC_A:
	  {
          // Interpret [[]] tag
	   STRING_LIST *out=new_string_list();
	   out->str=gc_a(sL->str,dest); sL->str=NULL;
	   output=push_sl_list(out,output);
	  }
	 break;
	 case TYPE_GC_B:
	  {
  	  // Interpret [>..[><]..<] tag
	   STRING_LIST *out=new_string_list();
	   out->str=gc_b(sL->str,dest); sL->str=NULL;
	   output=push_sl_list(out,output);
	  }
	 break;
    }
	output=rev_string_list(output);
#if defined(DEBUG)
 printf( "crunch: %d output chunks\n", strings_in_list(output) );
#endif
	return output;
}

void interpret( char *common, char *db,  char *dbpath, char *source, char *dest ) {
 char *code;
 char *s;
 int err=file_as_string( source, &code );
 if ( err==0 ) {
  sprintf( err_msg, "Problem with file %s", source );
  php_report( err_msg, dest );
 }
 init_sql(db);
 if ( gc_verify( code ) ) {
   STRING_LIST *sliced=NULL;
   STRING_LIST *output=NULL;
   // Segment level 1
   source=html_script_tag_normalize(code); // makes < script into <script and < /script> into </script>?  can this be done in a preprocessor?
   sliced=rev_string_list(code_slice(code,dest));
   if ( !sliced ) {
    sprintf( err_msg, "No code." );
    php_report( err_msg, dest );
   }
   else {
	output=crunch( sliced,dest );
	string_list_as_file(dest,output);
	free_string_list(output);
   }
 } else {
#if defined(DEBUG)
 printf( "Code did not verify:\n %s\nDid not pass verification\n", code );
#endif
   php_report( err_msg, dest );
 }

 close_sql();
 free(code);
}
