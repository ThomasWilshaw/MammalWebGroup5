import pymysql
import math

#TODO: Get blank classification number(s) from database (options) rather than using hard coded value(s) (86+87)
#      Calculate evenness, numerator is done need to get S

def aggregate_classifications(photo_ID,connection):
    c=connection.cursor()
    sql="SELECT * FROM animal where photo_id="+str(photo_ID)
    c.execute(sql)
    result=c.fetchall()
    
    speciesTally=dict()
    numClass=0
    nonBlanks=0
    for row in result:
        rowS=row['species']
        numClass+=1
        if rowS!=86 and rowS!=87:
            nonBlanks+=1
        
        if  rowS in speciesTally:
            speciesTally[rowS]=speciesTally[rowS]+1
        else:
            speciesTally[rowS]=1
    if numClass>0:
        values=list(speciesTally.values())
        keys=list(speciesTally.keys())
        
        species=keys[values.index(max(values))]

        #According to paper this is correct, strange that blanks are counted?
        support=speciesTally[species]/numClass
        
        blanks=(numClass-nonBlanks)/numClass
        
        if blanks==1:
            evenness=-1
        elif support==1:
            evenness=0
        else:
            pTot=0
            for s in speciesTally:
                if s!=86 and s!=87:
                    p=speciesTally[s]/(nonBlanks)
                    pTot+=p*math.log(p)
            print("p: ",pTot)
                    
    else:
        #Would need to add an unclassified option to database
        #Kinda nice to have all fields here to more easily see everything that needs doing
        species=-1
        evenness=-1
        blanks=-1
        support=-1
    #Test print
    #print(species,numClass,nonBlanks)
    
        
    sql="INSERT INTO nameofaggregateclasstablehere (%s,%s,%s,etc)"
    #c.execute(sql,(values (sepcies, evenness etc) here))
    #c.commit()

connection = pymysql.connect(host='localhost',user='root',password='toot',db='mammalwebdump',charset='utf8mb4',cursorclass=pymysql.cursors.DictCursor)
for id in range(2000):
    print("ID: ",id)
    aggregate_classifications(id,connection)

connection.close()
