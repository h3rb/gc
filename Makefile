
CC      =gcc
PROF    =-O -g3#      -pg -DUSE_MCHECK
L_FLAGS =$(PROF) -lcrypt -lpthread -ldl#     -lmcheck -static -DMALLOC_DEBUG
E_FLAGS =#            -Lefence/ -lefence -DMALLOC_DEBUG
O_FLAGS =$(PROF)
C_FLAGS =-Wall -Wstrict-prototypes -Wpointer-arith -Wno-char-subscripts \
         -Winline $(O_FLAGS)

.SUFFIXES:
.SUFFIXES: .c .o
# -L/usr/local/lib 


O_FILES = main.o sqlite3.o string.o files.o mem.o config.o sqlapi.o parse.o

GC.new: $(O_FILES)
	$(CC) $(L_FLAGS) -o GC *.o

clean:
	rm -f *.o GC
