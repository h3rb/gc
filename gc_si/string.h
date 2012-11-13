
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

#define STRING_SIZE 32768
#define LOWER(c)                   ((c) >= 'A' && (c) <= 'Z' ? (c)+'a'-'A' : (c))
#define UPPER(c)                   ((c) >= 'a' && (c) <= 'z' ? (c)+'A'-'a' : (c))
#define IS_VOWEL(c)             (c == 'A' || c == 'a' ||                     \
                                 c == 'E' || c == 'e' ||                     \
                                 c == 'I' || c == 'i' ||                     \
                                 c == 'O' || c == 'o' ||                     \
                                 c == 'U' || c == 'u'      )

typedef struct string_list STRING_LIST;

struct string_list {
 STRING_LIST *next;
 char *str;
 int type;
};

bool valid_characters( char *fn );
bool string_in_list( STRING_LIST *sl, char *s );
int strings_in_list( STRING_LIST *sl );
char *string_list_to_string( STRING_LIST *l ); // limited to STRING_SIZE
char *string_list_as_string( STRING_LIST *pString );
STRING_LIST *push_sl_list( STRING_LIST *s, STRING_LIST *list );
STRING_LIST *push_string_list( char *s, STRING_LIST *list );
STRING_LIST *new_string_list( void );
STRING_LIST *rev_string_list( STRING_LIST *old ); 
void free_string_list( STRING_LIST *pString );
char *ntos( float n, char *fmt );
void smash_char( char *str, char c );
bool str_cmp( const char *astr, const char *bstr );
bool prefix( const char *astr, const char *bstr ); 
int words( char *argument );
char * unpad( char * argument );
bool is_number( char *arg );
char *first_word( char *s, char *f );
char *uses_appositive( char *s, char *f );
char *first_Arg( char *s, char *f );
char * strupr( char * s );
char * strlwr( char * s );
bool has_keyword( const char *str, char *namelist );
char *skip_spaces( char *argument );
void append_file( char *file, char *str );
char *str_replace(char *str, char *old, char *new);
bool contains( char *astr, char c );
char *find_next( char *s, char *key );
int count_occurrances( char *a, char *needle );
char *gsubstr( char *string, char *start, char *end );
void scat( char *buf, char *fmt,  ... );
bool stristr(const char *String, const char *Pattern);
