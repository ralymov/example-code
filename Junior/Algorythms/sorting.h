#pragma once
#include <iostream>
#include<ctime>	
#include<math.h>
#include <random>
#include <time.h>
#include <stdio.h>
#include <stdlib.h>
int n=0;
int iteration=0;
const int N=1024;
using namespace std;
namespace DEATH {

	using namespace System;
	using namespace System::ComponentModel;
	using namespace System::Collections;
	using namespace System::Windows::Forms;
	using namespace System::Data;
	using namespace System::Drawing;
	using namespace System::IO;

	/// <summary>
	/// Сводка для Form1
	/// </summary>
	public ref class Form1 : public System::Windows::Forms::Form
	{
	public:
		Form1(void)
		{
			InitializeComponent();
		}

	protected:
		/// <summary>
		/// Освободить все используемые ресурсы.
		/// </summary>
		~Form1()
		{
			if (components)
			{
				delete components;
			}
		}
	private: System::Windows::Forms::TextBox^  masraz;
	protected: 

	private: System::Windows::Forms::Label^  label1;

	private: System::Windows::Forms::ComboBox^  comboBox1;
	private: System::Windows::Forms::Label^  label2;
	private: System::Windows::Forms::Button^  button2;
	private: System::Windows::Forms::Label^  label3;
	private: System::Windows::Forms::TextBox^  textBox1;
	private: System::Windows::Forms::TextBox^  textBox2;
	private: System::Windows::Forms::Label^  label4;
	private: System::Windows::Forms::Label^  label5;
	private: System::Windows::Forms::Label^  label6;
	private: System::Windows::Forms::TextBox^  textBox3;
	private: System::Windows::Forms::TextBox^  textBox4;
	private: System::Windows::Forms::ComboBox^  comboBox2;
	private: System::Windows::Forms::Label^  label7;
	private: System::Windows::Forms::Label^  label8;
	private: System::Windows::Forms::Label^  label9;
	private: System::Windows::Forms::Button^  button1;
	private: System::Windows::Forms::Button^  button3;
	private: System::Windows::Forms::Label^  label10;
	private: System::Windows::Forms::TextBox^  textBox5;
	private: System::Windows::Forms::Label^  label11;
	private: System::Windows::Forms::TextBox^  textBox6;
	private: System::Windows::Forms::Button^  button4;
	private: System::Windows::Forms::Button^  button5;

bool uslovie(double a)
{
	double b=pow(a,0.5);
	if ((b-int(b))==0)
	{return 1;}
	else return 0;
}
//-----------------------
void count(double A[], int B[], int &m, int n)
{
	int j=0;
	for (int i = 0; i<n; i++)
	{
		if (uslovie(A[i]) == 1)
		{
			B[j] = i;
			j++;
		}
	}
	m = j;
}
//-----------------
bool vvod(String^ S)
		{
			if (S->Length == 0) return 0;
	String^ s="";
	String^ number="0123456789";
	for(int i=0;i<S->Length;++i)
	{
	   s=S->Substring(i,1);
	   int j=number->IndexOf(s);
	   if (j==-1 || (s=="0" && S->Substring(0,1) == "0")) return 0; 
	}
	return 1;

		}

//--------------------------------------------------
void Vibor(double A[],int n,int &iteration)
{	
	int min=0;
	for(int i=0;i<n;i++)
	{	
		min=i;
		for(int j=i+1;j<n;j++)
		{	
			
			if(A[min]>A[j]&&uslovie(A[min])==1&&uslovie(A[j])==1)
			{
				min=j;
				iteration++;
			}
		}
		std::swap(A[i],A[min]);
	}
}
//-------------------------------------------
void vstavka(double A[],int n,int &iteration)
{	
	int j;
	for(int i=1;i<n;i++)
	{
		if(uslovie(A[i])==1)
		{
			j=i-1;
			while(j>-1)
			{
				if(A[i]<A[j]&&uslovie(A[j])==1)
				{
					std::swap(A[j],A[i]);
					if (j>0)
					i=j;
					iteration++;
				}
				j--;
			}
		}
	}
}
//--------------------------------------------------
void obmen(double A[],int n,int &iteration)
{	
	for(int i=0;i<n-1;i++)
	{
		for(int j=i+1;j<n;j++)
		{

			if ((A[i]>A[j])&&(uslovie(A[i])==1)&&(uslovie(A[j])==1))
			{	
				std::swap(A[i],A[j]);
				iteration++;
			}
		}
		
	}
}
//------------------------
void sort_bin(double A[], int n, int B[],int m,int &iteration)
{	
	int num, left, right, middle;
	for (int i=1; i<m; i++) 
	if (A[B[i-1]]>A[B[i]])
	{
		num = A[B[i]];
		left=0; 
		right=i-1; 
	do 
	{
		middle = (left + right) / 2; 
		if (A[B[middle]] < num ) 
		left = middle + 1; 
		else 
		right = middle - 1; 
	} 
	while (left <= right);
	for (int m= i-1; m>=left; m--)
	A[B[m+1]] = A[B[m]]; 
	A[B[left]] = num;
	iteration++;
	}
}
//---------------------------------------
void sort_shell(double A[], int n,int &iteration)
{	
	int d,j;
	d=n/2;
	while (d>0)
	{
		for (int i=0;i<n-d;i++)
		{
			j=i;
			while (j>=0&&A[j]>A[j+d]&&uslovie(A[j])&&uslovie(A[j+d]))
			{
				std::swap(A[j],A[j+d]);
				iteration++;
				j--;
			}
		}
		d/=2;
	}
}
void Hoar(double A[], int B[],int a,int b,int &iteration)
{	
	int opora, c(0);
	int f=a, l=b;
	opora=A[B[(f+l) / 2]];
	do
	{
		while (A[B[f]]<opora) 
			f++;
		while (A[B[l]]>opora) 
			l--;
		if (f<=l)
		{
			std::swap(A[B[f]],A[B[l]]);
			iteration++;
			f++;
			l--;
		}
	} while (f<l);
if (a<l) 
	Hoar(A, B, a, l,iteration);
if (f<b) 
	Hoar(A, B , f, b,iteration);
}



	private: System::Void Form1_Load(System::Object^  sender, System::EventArgs^  e) 
				{
					this->Text="Практика";
					MessageBox::Show("Сортирует только полные квадрат!", "Программа сортировки чисел");
					this->label1->Visible=0;
					this->label4->Visible=0;
					this->label3->Visible=0;
					this->label5->Visible=0;
					this->label6->Visible=0;
					this->label8->Visible=0;
					this->label9->Visible=0;
					this->label10->Visible=0;
					this->label11->Visible=0;
					this->textBox2->Visible=0;
					this->textBox3->Visible=0;
					this->textBox4->Visible=0;
					this->textBox1->Visible=0;
					this->textBox5->Visible=0;
					this->textBox6->Visible=0;
					this->comboBox2->Visible=0;
					this->button1->Visible=0;
					this->button5->Visible=0;
					this->button4->Visible=0;
					this->masraz->Visible=0;
				}
			
	

private: System::Void comboBox1_SelectedIndexChanged(System::Object^  sender, System::EventArgs^  e) {
		 }
private: System::Void button2_Click(System::Object^  sender, System::EventArgs^  e) {
					this->masraz->Visible=1;
							this->label1->Visible=1;
							this->label4->Visible=1;
							this->textBox2->Visible=1;
							this->label3->Visible=1;
							this->textBox1->Visible=1;
							this->button5->Visible=1;
					String ^S=masraz->Text;
					if (!(vvod(S)))
					{
						MessageBox::Show("Неверный формат введенных данных(Размер массива).Решение:Введите размер заново","Fatal Error");
					}
					else
					{
					n=System::Convert::ToInt32(masraz->Text);
					switch(comboBox1->SelectedIndex)
					{
						case 0: //с клавиатуры
						{	
							
							String ^SS="";
							array<String^>^ Mas = textBox2->Text->Split(' ');
							double A[N];
							for(int i=0;i<n;i++)
							{
								A[i]=System::Convert::ToInt32(Mas[i]);
								SS=SS->Format(SS+"{0} ", A[i]);
							}
							int k=SS->Length;
							int schet=0;
							int rrr=0;
							String ^podst="";
							podst=SS;
							while(podst!="")
								{
									rrr=podst->IndexOf(" ");
									podst=podst->Remove(0,rrr+1);
									schet++;
								}
							if(schet==n)
							textBox1->Text = SS;
							else MessageBox::Show("Несовпадение количества элементов","Введите нужное количество элементов");
							this->masraz->Visible=1;
							this->label1->Visible=1;
							this->label4->Visible=1;
							this->textBox2->Visible=1;
							this->label3->Visible=1;
							this->textBox1->Visible=1;
							this->button5->Visible=1;
						}break;
						case 1: //random
						{
							
							 String^ niz=textBox3->Text;
							 String^ verx=textBox4->Text;
							 if (vvod(niz)==1&&vvod(verx)==1)
							 {
							 int a=System::Convert::ToInt32(textBox3->Text);
							 int b=System::Convert::ToInt32(textBox4->Text);
							 if (n>N) MessageBox::Show("Максимальное количество элементов 1024! \nВведите правильное значение!","Ошибка");
							 else 
							 {
								if (a>b) std::swap(a,b);
								double A[N];
								String ^SS="";
								srand(time(NULL));
								for(int i=0;i<n;i++)
								{
									A[i]=rand()%(b-a+1)+a;
									SS=SS->Format(SS+"{0} ",A[i]);
								 }
								textBox1->Text = SS;
							 }
							 }
							 else MessageBox::Show("Неправильные границы","Введите другие значения");
							 this->masraz->Visible=1;
							this->label1->Visible=1;
							this->label5->Visible=1;
							this->label6->Visible=1;
							this->textBox3->Visible=1;
							this->textBox4->Visible=1;
							this->label3->Visible=1;
							this->textBox1->Visible=1;
							this->button5->Visible=1;
							
						}
						break;
						case 2: //fail
						{
							this->masraz->Visible=1;
							this->label1->Visible=1;
							this->label3->Visible=1;
							this->textBox1->Visible=0;
							this->button5->Visible=1;
							if (!(File::Exists("123.txt"))) MessageBox::Show("Невозможно прочитать файл - файл отсутствует! \n Проверьте введённое имя файла!","Ошибка");
							else 
							{
								StreamReader^ stream = File::OpenText("123.txt");
								String^ s = stream->ReadLine();
								this->textBox1->Text = s;
								delete (IDisposable^)stream;
								array<String^>^ TempMassive = textBox1->Text->Split(' ');
								n=TempMassive->GetLength(0)-1;
							 }	
						}
					
					}
					}
			 
		 }


private: System::Void button3_Click(System::Object^  sender, System::EventArgs^  e) {
			 this->Close();
		 }

private: System::Void button1_Click(System::Object^  sender, System::EventArgs^  e) {
			 double A[N];
			 int B[N];
			 int m;
			 array<String^>^ Mas = textBox1->Text->Split(' '); 
			 for(int i=0;i<n;i++)
			 {
				 A[i]=System::Convert::ToInt32(Mas[i]);
			 }
			 switch(comboBox2->SelectedIndex)
			 {
			 case 0:
				 {
					 Vibor(A,n,iteration);
					  
				 }break;
			 case 1:
				 {
					 vstavka(A,n,iteration);
				 }break;
			 case 2:
				 {
					 obmen(A,n,iteration);
				 }break;
			 case 3:
				 {
					 count(A,B,m,n);
					 sort_bin(A,n,B,m,iteration);
				 }break;
			 case 4:
				 {
					 sort_shell(A,n,iteration);
				 }break;
			 case 5:
				 {
					 count(A,B,m,n);
					 Hoar(A,B,0,m-1,iteration);
				 }break;
			
			 }
			 String^ Str="";
			 for(int i=0;i<n;++i) Str=Str->Format(Str+"{0} ",A[i]);
			 textBox5->Text = Str;
			 String^ text_iteration="";
			 text_iteration=text_iteration->Format(text_iteration+"{0} ",iteration);
			 textBox6->Text = text_iteration;
			 iteration=0;

		 }
private: System::Void button5_Click(System::Object^  sender, System::EventArgs^  e) {
			  this->textBox1->Text="";
		 }
private: System::Void button4_Click(System::Object^  sender, System::EventArgs^  e) {
			  this->textBox5->Text="";
		 }
};
}

