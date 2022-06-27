<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use Illuminate\Http\Request;
use App\Models\User;

class ProdutoController extends Controller
{

   
    public function index(){
        $search = request('search');

        if($search){
            $produtos = Produto::orWhere('tipo','like','%'.$search.'%')
            ->orWhere('marca','like','%'.$search.'%')->get();
            

        //     $produtos = Produto::orWhere(
        //         ['tipo','like','%'.$search.'%'],
        // ['marca','like','%'.$search.'%'])->get();
        }else{
            $produtos = Produto::all();
        }
        
        return view('welcome',['produtos'=>$produtos, 'search'=>$search]);
    }

    public function criar(){
        $search = request('search');
        $user = auth()->user();
        $acao = 'criar';
        $produtos = $user->produtos;

        $produtoAsParticipant = $user->produtoAsParticipant;
        return view('produtos.criar',['produtos'=>$produtos, 'produtoAsParticipant'=>$produtoAsParticipant, 'acao'=> $acao,'search'=>$search]);
    }


    public function store(Request $request){//store
        $produto = new Produto;

        $produto->id = $request->id;
        $produto->tipo =  $request->tipo;
        $produto->marca = $request->marca;
        $produto->tamanho = $request->tamanho;
        $produto->preco = $request->preco;

        if ($request->hasFile('imagem') && $request->file('imagem')->isValid()){
            $requestImagem = $request->imagem;
            $extensao = $requestImagem->extension();
            $nomeImagem = md5($requestImagem->getClientOriginalName().strtotime("now")).".".$extensao;
            $requestImagem->move(public_path('images/produtos'),$nomeImagem);
            $produto->imagem = $nomeImagem;
        }
        $user = auth()->user();
        $produto->user_id = $user->id;
        //$user->produtoAsParticipant()->attach($produto->id);

        $produto->save();
        return redirect('/');
    }


    // public function exibir(){
    //     //$produtos = Produto::all();
    //     return view('produtos.exibir');
    // }

    public function exibir($id){
        $search = request('search');
        $produto = Produto::findOrFail($id);
        $user = auth()->user();
       
        $eProprietario = false;
        $produtoOwner = User::where('id', $produto->user_id)->first()->toArray();
        if($user){                 
            if($produtoOwner){
                if($produtoOwner['id']==$user->id){
                    echo "<script>console.log(".$produtoOwner['id']." );</script>";
                    $eProprietario = true;
                }       
            }
        }
        

        return view('produtos.exibir',['produto'=>$produto,
         'produtoOwner'=>$produtoOwner, 'eProprietario'=>$eProprietario,'search'=>$search]);
    }

    public function dashboard() {
        $search = request('search');
        $user = auth()->user();

        $produtos = $user->produtos;

        $produtoAsParticipant = $user->produtoAsParticipant;

        return view('produtos.dashboard', ['produtos' => $produtos, 'produtoAsParticipant'=>$produtoAsParticipant,'search'=>$search]);

    }
    
    public function joinProduto($id) {
        $user = auth()->user();

        $user->produtoAsParticipant()->attach($id);

        $produto = Produto::findOrFail($id);

    }
    

    public function editar($id){
        $search = request('search');
        $user = auth()->user();
        $produto = Produto::findOrFail($id);
        $acao = 'editar';
        $produtos = $user->produtos;
        $produtoAsParticipant = $user->produtoAsParticipant;
        if($user->id!=$produto->user_id){
            return redirect('/dashboard');
        }
        return view('produtos.criar',['produto'=>$produto, 'produtos'=>$produtos, 
        'produtoAsParticipant'=>$produtoAsParticipant, 'acao'=>$acao,'search'=>$search]);
    }

    public function update(Request $request){
        
        $data = $request->all();
        if ($request->hasFile('imagem') && $request->file('imagem')->isValid()){
            $requestImagem = $request->imagem;
            $extensao = $requestImagem->extension();
            $nomeImagem = md5($requestImagem->getClientOriginalName().strtotime("now")).".".$extensao;
            $requestImagem->move(public_path('images/produtos'),$nomeImagem);
            $data['imagem'] = $nomeImagem;
        }
    
        Produto::findOrFail($request->id)->update($data);

        return redirect('/dashboard');
    }


    public function apagar($id){
        
        Produto::findOrFail($id)->delete();

        return redirect('/dashboard');
    }
}
