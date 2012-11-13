
#if defined(BSD)
#include <types.h>
#else
#include <sys/types.h>
#endif
#include <ctype.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <time.h>
#include <stdbool.h>

#include "string.h"
#include "files.h"
#include "mem.h"

bool valid_characters( char *fn ) {
 while ( *fn != '\0' ) switch ( *fn++ ) {
   case '0': case '1': case '2': case '3':
   case '4': case '5': case '6': case '7':
   case '8': case '9': case 'A': case 'B':
   case 'C': case 'D': case 'E': case 'F':
   case 'G': case 'H': case 'I': case 'J':
   case 'K': case 'L': case 'M': case 'N':
   case 'O': case 'P': case 'Q': case 'R':
   case 'S': case 'T': case 'U': case 'V':
   case 'W': case 'X': case 'Y': case 'Z':
   case 'a': case 'b': case 'c': case 'd':
   case 'e': case 'f': case 'g': case 'h':
   case 'i': case 'j': case 'k': case 'l':
   case 'm': case 'n': case 'o': case 'p':
   case 'q': case 'r': case 's': case 't':
   case 'u': case 'v': case 'w': case 'x':
   case 'y': case 'z': case '%': case '_':
   case '.': case '-':
   case '\n': case '\0': break;
 default: return false;                
 }    
 return true;
}

char *addsq( char *s ) {
 char sq[STRING_SIZE];
 int i=0,j=0;
 int len=strlen(s);
 for ( ; i<len; i++ ) {
  if ( s[i] == '\'' ) sq[j++] = '\\';
  sq[j++]=s[i];
 }
 sq[j]='\0';
 return str_dup(sq);
}

bool string_in_list( STRING_LIST *sl, char *s ) {
    bool result=false;
    for ( ; sl!=NULL; sl=sl->next ) if ( !str_cmp(s,sl->str) ) result=true;
    return result;
}

bool keyword_in_list( STRING_LIST *sl, char *s ) {
    bool result=false;
    for ( ; sl!=NULL; sl=sl->next ) if ( has_keyword(s,sl->str) ) result=true;
    return result;
}

int strings_in_list( STRING_LIST *sl ) {
   int count=0;
   STRING_LIST *n;
   for( n=sl; n!=NULL; n=n->next ) count++;
   return count;
}

STRING_LIST *push_sl_list( STRING_LIST *s, STRING_LIST *list ) {
 s->next=list;
 return s;
}

STRING_LIST *push_string_list( char *s, STRING_LIST *list ) {
 STRING_LIST *pString;
 pString = new_string_list();
 pString->str = s;
 pString->next = list; list=pString;
 return pString;
}

STRING_LIST *new_string_list( void ) {
 STRING_LIST *pString;
 pString=malloc(sizeof(STRING_LIST));
 pString->str = NULL;// str_dup( "" );
 pString->type=0;
 pString->next = NULL;
 return pString;
}

char *string_list_as_string( STRING_LIST *pString ) {
  char *final;
  int size=0;
  STRING_LIST *sL;
  for ( sL=pString; sL; sL=sL->next ) size+=strlen(sL->str);
  final=malloc(sizeof(char)*(size+1));
  for ( sL=pString; sL; sL=sL->next ) snprintf( final, size+1, "%s%s", final, sL->str );
  return final;
}

void string_list_as_file( char *target, STRING_LIST *out ) {
 STRING_LIST *p;
 FILE *fp;
 fp=fopen(target,"w");
 for ( p=out; p; p=p->next ) fwrite( p->str, sizeof(char), strlen(p->str), fp );
 fclose(fp);
}

STRING_LIST *rev_string_list( STRING_LIST *old ) {
 STRING_LIST *new=NULL, *o,*n;
 for ( o=old; o; o=n ) {
  n=o->next;
  new=push_sl_list( o, new );
 }
 return new;
}

char *ntos( float n, char *fmt ) {
    static char buf[STRING_SIZE];
    snprintf(buf,STRING_SIZE, fmt,n);
    return buf;
}

void smash_char( char *str, char c ) {
    for ( ; *str != '\0'; str++ ) if ( *str == c ) *str = '?';
    return;
}

bool str_cmp( const char *astr, const char *bstr ) {
    if ( astr == NULL ) return true;
    if ( bstr == NULL ) return true;
    for ( ; *astr || *bstr; astr++, bstr++ ) if ( LOWER(*astr) != LOWER(*bstr) ) return true;
    return false;
}

bool prefix( const char *astr, const char *bstr )
{
    if ( astr == NULL )	return true;
    if ( bstr == NULL )	return true;
    for ( ; *astr; astr++, bstr++ ) if ( LOWER(*astr) != LOWER(*bstr) ) return true;
    return false;
}

int words( char *argument )
{
    int total;
    char *s;
    total = 0;
    s = argument;
    while ( *s != '\0' )
    {
        if ( *s != ' ' )
        {
            total++;
            while ( *s != ' ' && *s != '\0' ) s++;
        }
        else s++;
    }
    return total;
}

char * unpad( char * argument )
{
    char buf[STRING_SIZE];
    char *s= argument;
    while ( *s == ' ' ) s++;
    strcpy( buf, s );
    s = buf;
    if ( *s != '\0' )
    {
        while ( *s != '\0' ) s++;
        s--;
        while( *s == ' ' )   s--;
        s++;
        *s = '\0';
    }
    free_string( argument );
    return str_dup( buf );
}

bool is_number( char *arg )
{
    bool period=false;
    if ( *arg == '\0' ) return false;
    if ( *arg == '-' ) {
       arg++;
       if ( !isdigit(*arg) && (*arg == '.' && period) ) return false;
	   if ( *arg=='.' ) period=true;
       arg++;
	 }
	period=false;
    for ( ; *arg != '\0'; arg++ ) {
 	 if ( !isdigit(*arg) || (period && *arg == '.') ) return false;
     if ( *arg=='.' ) period=true;
    }
    return true;
}

char *first_word( char *s, char *f )
{
  char cEnd;
  if ( s == NULL ) return "";
  while ( isspace(*s) || *s == '\n' || *s == '\r' || *s==',' ) s++;
  cEnd = ' ';
  if ( *s == '\'' || *s == '"' || *s == '\'' ) cEnd = *s++;
  while ( *s != '\0' )
  {
    if ( *s == '\n' ) { s++; if ( cEnd == ' ' ) break; }
    if ( *s == '\r' ) { s++; if ( cEnd == ' ' ) break; }
    if ( *s == cEnd ) { s++; break; }
    if ( cEnd != '"' && *s == '"'  ) { cEnd='"'; }
    *f = LOWER(*s);
    f++;
    s++;
  }
  *f = '\0';
  while ( isspace(*s) || *s == '\n' || *s == '\r' ) s++;
  return s;
}

char *first_Arg( char *s, char *f )
{
   char cEnd;
   if ( s == NULL ) return "";
   while ( isspace(*s) || *s == '\n' || *s == '\r' )
	s++;
   cEnd = ' ';
   if ( *s == '\'' || *s == '"' || *s == '\'' )
	cEnd = *s++;
   while ( *s != '\0' )
   {
    if ( *s == '\n' ) { s++; if ( cEnd == ' ' ) break; }
    if ( *s == '\r' ) { s++; if ( cEnd == ' ' ) break; }
    if ( *s == cEnd ) { s++; break; }
    if ( cEnd != '"' && *s == '"'  ) { cEnd='"'; }
    *f = (*s);
    f++;
    s++;
   }
   *f = '\0';
   while ( isspace(*s) || *s == '\n' || *s == '\r' )
	s++;
   return s;
}

char *uses_appositive( char *s, char *f )
{
  char cEnd;
  if ( s == NULL ) return "";
  while ( isspace(*s) || *s == '\n' || *s == '\r' || *s==',' ) s++;
  cEnd = ',';
  while ( *s != '\0' )
  {
    if ( *s == cEnd ) { s++; break; }
    *f = LOWER(*s);
    f++;
    s++;
  }
  *f = '\0';
  while ( isspace(*s) || *s == '\n' || *s == '\r' || *s==',' ) s++;
  if ( *s==';' ) return NULL;
  return s;
}

char * strupr( char * s )
{
   char * u=s;
   for( ; *u != '\0'; u++ ) *u = UPPER(*u);
   return s;
}

char * strlwr( char * s )
{
   char * u=s;
   for( ; *u != '\0'; u++ ) *u = LOWER(*u);
   return s;
}

bool has_keyword( const char *str, char *namelist )
{
 char name[2048];
 char *p;
 if ( str == NULL || namelist == NULL ) return false;
 for ( p = namelist; ; ) {
  p = first_word( p, name );
  if ( name[0] == '\0' ) return false;
  if ( !str_cmp( str, name ) ) return true;
 }
 for ( p = namelist; ; ) {
  p = first_word( p, name );
  if ( name[0] == '\0' ) return false;
  if ( !str_cmp( str, name ) ) return true;
 }
}

char *skip_spaces( char *argument )
{
 while ( *argument == ' ' )  argument++;
 return argument;
}

void append_file( char *file, char *str )
{
  FILE *fp;

  if ( ( fp = fopen( file, "a" ) ) == NULL ) perror( file );
  else
  {
	fprintf( fp, "%s", str );
	fclose( fp );
  }
  return;
}

char *str_replace(char *str, char *old, char *new) {
  int i, count = 0;
  int newlen = strlen(new);
  int oldlen = strlen(old);
 
  for (i = 0; str[i]; ++i)
    if (strstr(&str[i], old) == &str[i])
      ++count, i += oldlen - 1;
 
  char *ret = (char *) calloc(i + 1 + count * (newlen - oldlen), sizeof(char));
  if (!ret) return;
 
  i = 0;
  while (*str)
    if (strstr(str, old) == str)
      strcpy(&ret[i], new),
      i += newlen,
      str += oldlen;
    else
      ret[i++] = *str++;
  
  ret[i] = '\0';
  return ret;
}

bool contains( char *astr, char c )
{
    char *p;
    p=astr;
    while ( *p != '\0' ) if ( *(p++) == c ) return true;
    return false;
}

char *find_next( char *s, char *key ) {
 char *p,*r;
 p=s;
 while ( *p != '\0' ) {
  r=p;
  if ( *p==*key ) {
   char *q=key;
   bool f=true;
   while ( *q != '\0' && f ) {
    f=(LOWER(*q)==LOWER(*p));
    q++;
    p++;
   }
   if ( f ) return r;
  } else p++;
 }
 return s;
}

int count_occurrances( char *a, char *needle ) {
 int count=0;
 char *p;
 p=a;
 while ( *p != '\0' ) {
  if ( *p==*needle ) {
   char *q=needle;
   bool f=true;
   while ( *q != '\0' && f ) {
    f=(LOWER(*q)==LOWER(*p));
    q++;
    p++;
   }
   if ( f ) count++;
  } else p++;
 }
 return count;
}

char *gsubstr( char *string, char *start, char *end ) {
 char *new=malloc(sizeof(char)*((end-start)+1));
 char *p=new;
 while ( *start != '\0' && start != end ) { *p=*start; start++; p++; }
 *p='\0';
 return new;
}

void scat( char *buf, char *fmt,  ... ) {
 char b[16384];
 char *p,*q;
 va_list args;
 va_start(args, fmt);
 vsprintf(b, fmt, args);
 va_end(args);
 p=buf;
 while ( *p != '\0' ) p++;
 q=b;
 while ( *q != '\0' ) {
  *p=*q;
  q++;
  p++;
 }
 *p='\0';
}

void trimlast( char *buf ) {
 char *p;
 p=buf;
 if ( *p == '\0' ) return;
 while ( *(p+1) !='\0' ) p++;
 *p='\0';
}


bool stristr(const char *String, const char *Pattern) {
 char *pptr, *sptr, *start;
 for (start = (char *)String; *start != '\0'; start++) {
  for ( ; ((*start!='\0') && (UPPER(*start) != UPPER(*Pattern))); start++);
  if ('\0' == *start) return false;
  pptr = (char *)Pattern;
  sptr = (char *)start;
  while (UPPER(*sptr) == UPPER(*pptr)) {
   sptr++;
   pptr++;
   if ('\0' == *pptr) return true;
  }
 }
 return false;
}
