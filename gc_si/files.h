#include <sys/stat.h>
#include <math.h>
#include <stdbool.h>
#include <stdarg.h>

void OUTPUT(char *fmt, ...);
bool file_exists( char *name );
void fread_to_eol( FILE *fp );
char *fread_string_eol( FILE *fp );
STRING_LIST *fread_file( char *filename );
int new_folder( char *path );
int file_as_string(char *filename, char **result);
void string_as_file(char *filename, char *content);
