#include <iostream>
#include <fstream>
#include<ctime>
#include<string>
using namespace std;
struct Node
{
	int number;//область данных
	string fam;
	int mark;
	Node *next, *prev;//указатель на следующий узел
};
typedef struct Node *Pnode;//тип указатель на узел
//---------------------------
void MENU()
{
	cout << endl << "Автор программы:Алымов Роман" << endl << "Программа, работающая со списками" << endl << endl;
	cout << "Введите цифру для соответствующего действия:" << endl << "1-создание и заполнение списка с клавиатуры" << endl << "2-создание и заполнение списка случайными числами" << endl;
	cout << "3-запись в файл" << endl << "4-загрузка из файла" << endl << "5-просмотр списка" << endl << "6-удаление элемента" << endl << "7-вставка элемента после" << endl;
	cout << "8-изменение элемента" << endl << "9-удаление списка" <<endl<<"10-перестановка 2 элементов местами"<< endl << "0-выход из программы" << endl << endl;
}
//---------------------------
Pnode CreateNode(int num, string fami,int mark)
{
	Pnode NewNode = new Node;
	NewNode->number = num;
	NewNode->fam = fami;
	NewNode->mark = mark;
	NewNode->next = NULL;
	NewNode->prev = NULL;
	return NewNode;
}
//---------------------------
void del_all(Pnode &Head)
{
	Pnode q = Head;
	while (Head)
	{
		Head = q->next;
		delete q;
		q = Head;
	}
}
//----------------------------
void keyboard(Pnode &Head, int N, Pnode &Tail)//заполнение списка с клавиатуры
{
	int num, mark;
	string fami;
	do
	{	
		cout << "Введите номер класса " << 1 << "-ого ученика: ";
		cin.clear();
		cin.sync();
		cin >> num;
	} while (cin.fail());
	cout << "Введите фамилию " << 1 << "-ого ученика: ";
    cin.sync();
	getline(cin, fami);
	do
	{
		cout << "Введите оценку " << 1 << "-ого ученика: ";
		cin.clear();
		cin.sync();
		cin >> mark;
	} while (cin.fail());
	cout << endl << endl;
	Pnode now = CreateNode(num,fami,mark);
	Head = now;
	Tail = now;
	for ( int i=0; i < N-1; i++)
	{
		do
		{
			cout << "Введите номер класса " << i+2 << "-ого ученика: ";
			cin.clear();
			cin.sync();
			cin >> num;
		} while (cin.fail());
		cout << "Введите фамилию " << i+2 << "-ого ученика: ";
		cin.sync();
		getline(cin, fami);
		do
		{
			cout << "Введите оценку " << i+2 << "-ого ученика: ";
			cin.clear();
			cin.sync();
			cin >> mark;
		} while (cin.fail());
		cout << endl << endl;
		Pnode sled = CreateNode(num,fami,mark);
		now->next = sled;
		Tail = sled;
		sled->prev = now;
		now = sled;
	}
}
//---------------------------
void display(Pnode &Head,Pnode &Tail)
{
	int i = 0;
	Pnode q = Head;
		while (q)
		{
			i++;
			cout << endl << i << "-ый ученик: Номер класса:" << q->number << ".Фамилия: " << q->fam << ".Оценка за четверть:" << q->mark << endl;
			q = q->next;
		}
}
//----------------------------
Pnode Find(Pnode &Head, string name,Pnode &Tail)
{
	Pnode q = Head;
	if (q != NULL)
	{
		while (q && (q->fam != name))//поиск по фамилии
		q = q->next;
		return q;//возврашает адрес узла(не номер)
	}
	else 
	{
		Head = NULL;
		Tail = NULL;
	}
}
//----------------------------------
Pnode Walk(Pnode Head, int N) //передвижение по списку с возвращением адреса по позиции
{
	int i = 0;
	Pnode q = Head;
	while (q && i != (N-1))
	{
		q = q->next;
		++i;
	}
	return q;
}
//----------------------------------
int size(Pnode Head)
{
	int i(0);
	Pnode q = Head;
	while (q)
	{
		q = q->next;
		i++;
	}
	return i;
}
//----------------------------------
void WriteFile(ofstream &ToFile, Pnode Head)
{
	if (ToFile)
	{
		Pnode q = Head;
		int num, mark;
		string fami;
		while (q)
		{
			num = q->number;
			fami = q->fam;
			mark = q->mark;
			ToFile << num << " " << fami << " " << mark;
			if (q->next != NULL)
				ToFile << endl;
			q = q->next;
		}
	}
	else
		cout << "Потока нет" << endl;

}
//--------------------------------
void ReadFile(ifstream &OutFile, Pnode &Head, Pnode &Tail)
{
	if (OutFile)
	{
		int num = 0;
		int mark = 0;
		string fami;
		OutFile >> num;
		OutFile >> fami;
		OutFile >> mark;
		Pnode now = CreateNode(num,fami,mark);
		Head = now;
		Tail = now;
		while (OutFile.peek() != EOF)
		{
			OutFile >> num;
			OutFile >> fami;
			OutFile >> mark;
			Pnode sled = CreateNode(num,fami,mark);
			now->next = sled;
			Tail = sled;
			sled->prev = now;
			now = sled;
		}
	}
}
//--------------------------------
void del(Pnode &Head, Pnode &Tail, int N)
{
	Pnode OldNode = Head;
	if (OldNode != NULL)
	{
		for (int i = 0; i < N - 1; i++)
		{
			OldNode = OldNode->next;
		}
		if (Head == OldNode)
		{
			Head = OldNode->next;
			if (Head) Head->prev = NULL;
			else Tail = NULL;
		}
		else
		{
			OldNode->prev->next = OldNode->next;
			if (OldNode->next) OldNode->next->prev = OldNode->prev;
			else Tail = NULL;
		}
		delete OldNode;
	}
	else
	{
		Head = NULL;
		Tail = NULL;
	}
}

//----------------------------
void insert_first(Pnode NewNode, Pnode &Head, Pnode &Tail)
{
	NewNode->next = Head;
	NewNode->prev = NULL;
	if (Head) Head->prev = NewNode;
	Head = NewNode;
	if (!Tail) Tail = Head;
}
//-----------------------------
void insert_last(Pnode &Head, Pnode &Tail, Pnode NewNode)
{
	NewNode->prev = Tail;
	NewNode->next = NULL;
	if (Tail) Tail->next = NewNode;
	Tail = NewNode;
	if (!Head) Head = Tail;
}
//-----------------------------
void insert_after(Pnode NewNode, Pnode &Head, Pnode &Tail, int N)
{
	Pnode q = Head;
	if (N == 0)
	{
		insert_first(NewNode, Head, Tail);
	}
	else
	{
		for (int i = 0; i < N - 1; i++)
		{
			q = q->next;
		}
		if (!q->next) insert_last(Head, Tail, NewNode);
		else
		{
			NewNode->next = q->next;
			NewNode->prev = q;
			q->next->prev = NewNode;
			q->next = NewNode;
		}
	}
	
}
//----------------------------
void change(int k, int number,int mark,string fami, Pnode Head)
{
	Pnode q = Head;
	for (int i = 0; i < k - 1; i++)
	{
		q = q->next;
	}
	q->number = number;
	q->fam = fami;
	q->mark = mark;
}
//--------------------------------
//НАЧАЛО НОВЫХ ЗАДАНИЙ(НЕПОНЯТНЫХ)
void sWap(Pnode &p, Pnode &t,Pnode &Head,Pnode &Tail)
{
	
	Pnode p1, p2, t1, t2;
	if (t->next == p)
	{
		p1 = t;
		t = p;
		p = p1;
	}
	p1 = p->next;
	p2 = p->prev;
	t1 = t->next;
	t2 = t->prev;

	if (p->next == t)
	{
		p->prev = t;
		p->next = t1;
		t->prev = p2;
		t->next = p;
		if (p2 != NULL)
			p2->next = t;
		if (t1 != NULL)
			t1->prev = p;

	}
	else
	{
		p->prev = t2;
		p->next = t1;
		t->prev = p2;
		t->next = p1;
		if (p2 != NULL)
			p2->next = t;
		if (t1 != NULL)
			t1->prev = p;
		if (p1 != NULL)
			p1->prev = t;
		if (t2 != NULL)
			t2->next = p;
	}
}
//--------------------------------
void vstavit(Pnode NewNode,Pnode &Head, Pnode &Tail,int N)
{
	Pnode q = Head;
	int k = size(Head);
	if (N == 1 || N == 0)
		insert_first(NewNode, Head, Tail);
	else
	{
		if (N>k) insert_last(Head, Tail, NewNode);
		else
		{
			for (int i = 0; i < N - 1; i++)
			{
				if (q->next != NULL)
				{
					q = q->next;
				}
			}
			NewNode->next = q;
			NewNode->prev = q->prev;
			q->prev->next = NewNode;
			q->prev = NewNode;
		}
	}
}
//--------------------------------
int main()
{
	setlocale(0, "russian");
	Pnode Head(NULL), Tail(NULL);//указатель на начало списка и конец списка
	char fail[] = "spisok";
	//МЕНЮ
	int c;
	MENU();
	do
	{
		cin.clear();
		cin.sync();
		cin >> c;
		{
			switch (c)
			{
			case 1:
			{
					  int N;
					  do
					  {
						  cout << endl << "Введите количество элементов списка" << endl;
						  cin.clear();
						  cin.sync();
						  cin >> N;
					  } while (cin.fail()||N<=0);
					  keyboard(Head, N, Tail);
					  cout << endl << endl << "Заполнение завершено." << endl << endl;
			}
				break;
			case 2:
			{
					 

			}
				break;
			case 3:
			{
					  ofstream ToFile("List.txt");
					  WriteFile(ToFile, Head);
					  ToFile.close();
					  cout << "Список записан в файл." << endl;
			}
				break;
			case 4:
			{
					  ifstream Out("List.txt");
					  ReadFile(Out, Head, Tail);
					  Out.close();
					  cout << "Список загружен из файла." << endl;
			}
				break;
			case 5:
			{
					  display(Head, Tail);
					  cout << endl << "Список выведен" << endl << endl;
			}
				break;
			case 6:
			{
					  int N = 0;
					  do
					  {
						  cout << endl << "Введите номер элемента, который нужно удалить" << endl;
						  cin.clear();
						  cin.sync();
						  cin >> N;
					  } while (cin.fail());
					  Pnode q = Walk(Head, N);
					  if (q)
					  {
						  del(Head, Tail, N);
						  cout << "Удаление завершеное" << endl;
					  }
					  else cout << "Такого элемента нет" << endl;


			}
				break;
			case 7:
			{
					  int number = 0, mark = 0;
					  string fami;
					  cout << "Введите данные нового ученика для вставки" << endl;
					  do
					  {
						  cout << "Введите номер класса ";
						  cin.clear();
						  cin.sync();
						  cin >> number;
					  } while (cin.fail());

					  cout << "Введите фамилию ";
					  cin.sync();
					  getline(cin, fami);
					  do
					  {
						  cout << "Введите оценку ";
						  cin.clear();
						  cin.sync();
						  cin >> mark;
					  } while (cin.fail());
					  cout << endl << endl;
					  int N = 0;
					  do
					  {
						  cout << "Номер элемента для вставки ";
						  cin.clear();
						  cin.sync();
						  cin >> N;
					  } while (cin.fail());
					  Pnode NewNode = CreateNode(number, fami, mark);
					  vstavit(NewNode, Head, Tail, N);
					  cout << "Элемент вставлен" << endl;
			}
				break;
			case 8:
			{
					  int k = 0, number = 0, mark = 0;
					  string fami;
						  do
						  {		cout << "Введите номер ученика, которого нужно изменить" << endl;
								cin.clear();
								cin.sync();
								cin >> k;
					  } while (cin.fail()||k<0);
					  

					  Pnode q = Walk(Head, k);
					  if (q)
					  {
						  do
						  {
							  cout << "Введите номер асса ";
							  cin.clear();
							  cin.sync();
							  cin >> number;
						  } while (cin.fail());

						  cout << "Введите фамилию ";
						  cin.sync();
						  getline(cin, fami);
						  do
						  {
							  cout << "Введите оценку ";
							  cin.clear();
							  cin.sync();
							  cin >> mark;
						  } while (cin.fail());
						  cout << endl << endl;
						  change(k, number, mark, fami, Head);
						  cout << "Замена совершена" << endl;
					  }
					  else cout << "Такого нет" << endl;
			}
				break;
			case 9:
			{
					  del_all(Head);
					  cout << "Удаление завершено" << endl;
			}
				break;
			case 10:
			{
					   string name1, name2;
					   Pnode a1, a2;
					   cout << "Введите фамилию для поиска первого элемента замены" << endl;
					   cin.sync();
					   getline(cin, name1);
					   a1 = Find(Head, name1, Tail);
					   cout << "Введите фамилию для поиска второго элемента замены" << endl;
					   cin.sync();
					   getline(cin, name2);
					   a2 = Find(Head, name2, Tail);
					   sWap(a1, a2, Head, Tail);
					   cout << Head->fam << " " << Tail->fam << endl;
			}
			case 0:
			{
					  del_all(Head);
					  system("pause");
					  return 0;
			}

				break;
			default:break;
			}
		} while (cin.fail() && c == 0);
		MENU();
	}
	while (1);
	
	system("pause");
	return 0;
}