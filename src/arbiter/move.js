import { copyPosition } from "../helper"

export const movePiece = ({position,piece,rank,file,x,y}) => {

    const newPosition = copyPosition(position)

    if(piece.endsWith('k') && Math.abs(y - file) > 1){ // Enroques
        if (y === 2){ // Enroque largo
            newPosition[rank][0] = ''
            newPosition[rank][3] = piece.startsWith('w') ? 'wr' : 'br'
        }
        if (y === 6){ // Enroque corto
            newPosition[rank][7] = ''
            newPosition[rank][5] = piece.startsWith('w') ? 'wr' : 'br'
        }
    }
    
    newPosition[rank][file] = ''
    newPosition[x][y] = piece
    return newPosition
}

export const movePawn = ({position,piece,rank,file,x,y}) => {
    const newPosition = copyPosition(position)

    // Comprobar que comer al paso captura una celda vacia
    // Detectar y borrar el peon comido
    if (!newPosition[x][y] && x !== rank && y !== file) 
        newPosition[rank][y] = ''

    newPosition[rank][file] = ''
    newPosition[x][y] = piece
    return newPosition
}