<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;
use App\Comment;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic;

class Comments extends Component
{
    use WithPagination;
    public $newComment;
    public $image;

    //import image
    protected $listeners = ['fileUpload' => 'handleFileUpload'];
    public function handleFileUpload($imageData)
    {
        $this->image = $imageData;
    }

    //public $comments;
    //import depuis la base de donnée
    /*
    public function mount()
    {
        $BDcomments = Comment::latest()->get();
        $this->comments = $BDcomments;
    }
    */
 
    //message erreur instantané 
    public function updated($field)
    {
        $this->validateOnly($field, ['newComment' => 'required|min:5|max:255']);

    }

    //Enregistré dans la base de donnée
    public function AddComment()
    {
        $this->validate(['newComment' => 'required|min:5|max:255']);
        $image = $this->storeImage();
        $createdcomment = Comment::create(
            [
                'body' => $this->newComment,
                'user_id' => 1,
                'support_ticket_id' => 1,
                'image' => $image
            ]
            );

        //concatiner dans une collection
        //$this->comments->prepend($createdcomment);

        $this->newComment="";
        $this->image="";

        //message flache
        session()->flash('message','Commentaire ajouté');
    }
    
    public function storeImage()
    {
       if(!$this->image)
       {
        return null;
       }
       //utilisation de intervention librairie pour avoir l'image
       $img = ImageManagerStatic::make($this->image)->encode('jpg');
       //crée un nom pour l'image
       $name = str::random(). '.jpg';
       //enregistré l'image avec le nom et l'image
       storage::disk('public')->put($name, $img); 
       return $name;
    }      

    //fonction url image dans public storage
    public function getImagePathAttribute()
    {
        return Storage::disk('public')->url($this->image);
    }


    //fonction supprimé commentaire
    public function remove($commentId)
    {
        $comment = Comment::find($commentId);

        //supprimé de la bd
        $comment->delete();
        //supprimé de la page
        //$this->comments = $this->comments->except($commentId);

        //supprimé l'image
        Storage::disk('public')->delete($comment->image);

        //flash message
        session()->flash('message','Commentaire supprimé');
    }

    public function render()
    {
        return view('livewire.comments', ['comments' => Comment::latest()->paginate(2)]);
    }
}
